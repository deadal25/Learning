import sys
import os
import json
import argparse
import datetime
import openpyxl

def format_ordinal_date(date_str):
    """
    Format a YYYY-MM-DD string into English format with ordinal suffix.
    Example: 2026-07-13 -> July 13th, 2026
    """
    try:
        dt = datetime.datetime.strptime(date_str, "%Y-%m-%d")
    except Exception:
        dt = datetime.datetime.now()
        
    day = dt.day
    if 11 <= day <= 13:
        suffix = "th"
    else:
        suffix = {1: "st", 2: "nd", 3: "rd"}.get(day % 10, "th")
        
    return dt.strftime(f"%B {day}{suffix}, %Y")

def main():
    parser = argparse.ArgumentParser(description="Export English Attendance Excel based on Absensi.xlsx template")
    parser.add_argument("--template", required=True, help="Path to Absensi.xlsx template")
    parser.add_argument("--output", required=True, help="Path to output .xlsx file")
    parser.add_argument("--date", default=datetime.datetime.now().strftime("%Y-%m-%d"), help="Selected attendance date YYYY-MM-DD")
    parser.add_argument("--week", default="all", help="all, 1, 2, 3, or 4")
    parser.add_argument("--class-sheet", default="all", help="all or specific sheet name e.g. Class B1")
    parser.add_argument("--teacher", default="Miss Sarah Jenkins", help="Teacher name")
    parser.add_argument("--subject", default="English", help="Subject name")
    parser.add_argument("--students-json", default="[]", help="JSON string or path to JSON file containing students data")

    args = parser.parse_args()

    if not os.path.exists(args.template):
        print(json.dumps({"success": False, "error": f"Template not found: {args.template}"}))
        sys.exit(1)

    # Load students data
    students = []
    if os.path.exists(args.students_json):
        with open(args.students_json, "r", encoding="utf-8") as f:
            students = json.load(f)
    else:
        try:
            students = json.loads(args.students_json)
        except Exception:
            students = []

    # Load workbook preserving formulas and formatting
    wb = openpyxl.load_workbook(args.template, data_only=False)

    if args.class_sheet != "all":
        matched_sheet = None
        for sname in wb.sheetnames:
            if sname.strip().lower() == args.class_sheet.strip().lower():
                matched_sheet = sname
                break
        if matched_sheet:
            for sname in list(wb.sheetnames):
                if sname != matched_sheet:
                    del wb[sname]

    formatted_date = format_ordinal_date(args.date)

    week_titles = {
        "1": "1ST WEEK",
        "2": "2ND WEEK",
        "3": "3RD WEEK",
        "4": "4TH WEEK",
    }

    selected_week = str(args.week).strip().lower()

    if selected_week in ["1", "2", "3", "4"]:
        # User requested ONLY ONE SINGLE WEEK (e.g. Week 1 only)
        # We reuse the top block (rows 1-22), update title banner, period, data, and DELETE row 23 onward!
        # This guarantees that the Excel file ONLY contains that specific week!
        w_title = week_titles[selected_week]

        for sheet_name in wb.sheetnames:
            ws = wb[sheet_name]

            # 1. Update Title Banner (A1:N2)
            ws["A1"] = w_title

            # 2. Update Class, Teacher, Period, Subject
            clean_class_name = sheet_name.strip()
            ws["C4"] = clean_class_name
            ws["G4"] = args.teacher
            ws["C5"] = formatted_date
            ws["G5"] = args.subject

            # 3. Populate student rows 8 to 22
            for i in range(15):
                row_idx = 8 + i
                ws.cell(row=row_idx, column=1).value = float(i + 1)
                
                if i < len(students):
                    std = students[i]
                    std_name = std.get("name", "")
                    status = std.get("status")
                    notes = std.get("notes") or ""
                    feedback = std.get("feedback") or ""

                    ws.cell(row=row_idx, column=2).value = std_name

                    if notes:
                        ws.cell(row=row_idx, column=3).value = notes
                    elif feedback:
                        ws.cell(row=row_idx, column=3).value = feedback

                    m1 = std.get("m1")
                    m2 = std.get("m2")
                    m3 = std.get("m3")
                    m4 = std.get("m4")

                    if m1 is not None:
                        ws.cell(row=row_idx, column=4).value = float(m1)
                    elif status == "hadir":
                        ws.cell(row=row_idx, column=4).value = 85.0
                    elif status == "izin_keterangan":
                        ws.cell(row=row_idx, column=4).value = 75.0
                    elif status == "izin_tanpa_keterangan":
                        ws.cell(row=row_idx, column=4).value = 0.0

                    if m2 is not None: ws.cell(row=row_idx, column=5).value = float(m2)
                    if m3 is not None: ws.cell(row=row_idx, column=6).value = float(m3)
                    if m4 is not None: ws.cell(row=row_idx, column=7).value = float(m4)

                    fluency = std.get("fluency")
                    grammar = std.get("grammar")
                    pronunciation = std.get("pronunciation")
                    vocabulary = std.get("vocabulary")

                    if fluency is not None: ws.cell(row=row_idx, column=9).value = float(fluency)
                    if grammar is not None: ws.cell(row=row_idx, column=10).value = float(grammar)
                    if pronunciation is not None: ws.cell(row=row_idx, column=11).value = float(pronunciation)
                    if vocabulary is not None: ws.cell(row=row_idx, column=12).value = float(vocabulary)
                else:
                    ws.cell(row=row_idx, column=2).value = None

                # Keep formulas intact
                ws.cell(row=row_idx, column=8).value = f"=(D{row_idx}+E{row_idx}+F{row_idx}+G{row_idx})/4"
                ws.cell(row=row_idx, column=13).value = f"=(I{row_idx}+J{row_idx}+K{row_idx}+L{row_idx})/4"
                ws.cell(row=row_idx, column=14).value = f"=((H{row_idx}+M{row_idx})/2)"

            # 4. Remove all merged cells with min_row >= 23
            to_remove = [m for m in list(ws.merged_cells.ranges) if m.min_row >= 23]
            for m in to_remove:
                ws.merged_cells.ranges.remove(m)

            # 5. Delete all rows from 23 onward so ONLY Week 1 (rows 1-22) is in the Excel file!
            if ws.max_row > 22:
                ws.delete_rows(23, ws.max_row - 22 + 5)

    else:
        # User requested "all" weeks (All 4 weeks)
        weeks_meta = {
            "1": {"start_row": 8, "period_cell": "C5", "teacher_cell": "G4", "subject_cell": "G5", "class_cell": "C4"},
            "2": {"start_row": 31, "period_cell": "C28", "teacher_cell": "G27", "subject_cell": "G28", "class_cell": "C27"},
            "3": {"start_row": 54, "period_cell": "C51", "teacher_cell": "G50", "subject_cell": "G51", "class_cell": "C50"},
            "4": {"start_row": 77, "period_cell": "C74", "teacher_cell": "G73", "subject_cell": "G74", "class_cell": "C73"},
        }

        for sheet_name in wb.sheetnames:
            ws = wb[sheet_name]
            clean_class_name = sheet_name.strip()

            for w_key, meta in weeks_meta.items():
                ws[meta["class_cell"]] = clean_class_name
                ws[meta["teacher_cell"]] = args.teacher
                ws[meta["subject_cell"]] = args.subject
                ws[meta["period_cell"]] = formatted_date

            for i in range(15):
                row_idx = 8 + i
                if i < len(students):
                    std = students[i]
                    std_name = std.get("name", "")
                    status = std.get("status")
                    notes = std.get("notes") or ""
                    feedback = std.get("feedback") or ""

                    ws.cell(row=row_idx, column=2).value = std_name

                    for w_key in ["1", "2", "3", "4"]:
                        curr_row = weeks_meta[w_key]["start_row"] + i
                        if w_key != "1":
                            ws.cell(row=curr_row, column=2).value = f"=B8"

                        if notes:
                            ws.cell(row=curr_row, column=3).value = notes
                        elif feedback:
                            ws.cell(row=curr_row, column=3).value = feedback

                        m1 = std.get("m1")
                        m2 = std.get("m2")
                        m3 = std.get("m3")
                        m4 = std.get("m4")

                        if m1 is not None: ws.cell(row=curr_row, column=4).value = float(m1)
                        elif status == "hadir": ws.cell(row=curr_row, column=4).value = 85.0
                        elif status == "izin_keterangan": ws.cell(row=curr_row, column=4).value = 75.0
                        elif status == "izin_tanpa_keterangan": ws.cell(row=curr_row, column=4).value = 0.0

                        if m2 is not None: ws.cell(row=curr_row, column=5).value = float(m2)
                        if m3 is not None: ws.cell(row=curr_row, column=6).value = float(m3)
                        if m4 is not None: ws.cell(row=curr_row, column=7).value = float(m4)

                        ws.cell(row=curr_row, column=8).value = f"=(D{curr_row}+E{curr_row}+F{curr_row}+G{curr_row})/4"

                        fluency = std.get("fluency")
                        grammar = std.get("grammar")
                        pronunciation = std.get("pronunciation")
                        vocabulary = std.get("vocabulary")

                        if fluency is not None: ws.cell(row=curr_row, column=9).value = float(fluency)
                        if grammar is not None: ws.cell(row=curr_row, column=10).value = float(grammar)
                        if pronunciation is not None: ws.cell(row=curr_row, column=11).value = float(pronunciation)
                        if vocabulary is not None: ws.cell(row=curr_row, column=12).value = float(vocabulary)

                        ws.cell(row=curr_row, column=13).value = f"=(I{curr_row}+J{curr_row}+K{curr_row}+L{curr_row})/4"
                        ws.cell(row=curr_row, column=14).value = f"=((H{curr_row}+M{curr_row})/2)"

    os.makedirs(os.path.dirname(os.path.abspath(args.output)), exist_ok=True)
    wb.save(args.output)

    print(json.dumps({
        "success": True,
        "output": args.output,
        "sheets": wb.sheetnames,
        "week": selected_week,
        "students_count": len(students)
    }))

if __name__ == "__main__":
    main()
