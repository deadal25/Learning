import sys
import os
import json
import argparse
import datetime
import openpyxl
from copy import copy

def format_period_label(month_key, custom_period=None):
    """
    Format a period label for English teaching months.
    """
    if custom_period:
        return custom_period
    
    month_titles = {
        "1": "Bulan Ke-1 (1st Month)",
        "2": "Bulan Ke-2 (2nd Month)",
        "3": "Bulan Ke-3 (3rd Month)",
        "4": "Bulan Ke-4 (4th Month)",
        "5": "Bulan Ke-5 (5th Month)",
        "all": "Bulan 1 - Bulan 5",
    }
    return month_titles.get(str(month_key).lower(), f"Bulan Ke-{month_key}")

def clone_block(ws, src_start, dst_start, rows_count=23):
    """
    Clone a block of rows in an openpyxl worksheet, copying cell values, styles,
    row heights, and merged cell ranges with the appropriate offset.
    """
    offset = dst_start - src_start

    # 1. Copy row heights and cell attributes
    for r in range(rows_count):
        src_row = src_start + r
        dst_row = dst_start + r

        if src_row in ws.row_dimensions:
            ws.row_dimensions[dst_row].height = ws.row_dimensions[src_row].height

        for c in range(1, 15):
            src_cell = ws.cell(row=src_row, column=c)
            dst_cell = ws.cell(row=dst_row, column=c)

            if src_cell.has_style:
                dst_cell.font = copy(src_cell.font)
                dst_cell.border = copy(src_cell.border)
                dst_cell.fill = copy(src_cell.fill)
                dst_cell.number_format = copy(src_cell.number_format)
                dst_cell.protection = copy(src_cell.protection)
                dst_cell.alignment = copy(src_cell.alignment)

            dst_cell.value = src_cell.value

    # 2. Copy merged ranges belonging to the source block
    for m in list(ws.merged_cells.ranges):
        if src_start <= m.min_row < src_start + rows_count:
            new_min_row = m.min_row + offset
            new_max_row = m.max_row + offset
            new_min_col = m.min_col
            new_max_col = m.max_col
            ws.merge_cells(start_row=new_min_row, start_column=new_min_col,
                           end_row=new_max_row, end_column=new_max_col)

def main():
    parser = argparse.ArgumentParser(description="Export English Grades & Feedback Excel based on Absensi.xlsx template")
    parser.add_argument("--template", required=True, help="Path to Absensi.xlsx template")
    parser.add_argument("--output", required=True, help="Path to output .xlsx file")
    parser.add_argument("--month", default="all", help="all, 1, 2, 3, 4, or 5")
    parser.add_argument("--class-sheet", default="all", help="all or specific class sheet e.g. 'Class B1'")
    parser.add_argument("--teacher", default="Guru Bahasa Inggris", help="Teacher name")
    parser.add_argument("--subject", default="English", help="Subject name")
    parser.add_argument("--period", default=None, help="Custom period string e.g. 'July 2026'")
    parser.add_argument("--grades-json", required=True, help="Path to JSON file or raw JSON containing grades data")

    args = parser.parse_args()

    if not os.path.exists(args.template):
        print(json.dumps({"success": False, "error": f"Template not found: {args.template}"}))
        sys.exit(1)

    # Load grades data
    if os.path.exists(args.grades_json):
        with open(args.grades_json, "r", encoding="utf-8") as f:
            grades_data = json.load(f)
    else:
        try:
            grades_data = json.loads(args.grades_json)
        except Exception as e:
            print(json.dumps({"success": False, "error": f"Invalid JSON data: {str(e)}"}))
            sys.exit(1)

    # Load workbook preserving formulas and formatting
    wb = openpyxl.load_workbook(args.template, data_only=False)

    # Filter class sheet if a specific class is requested
    requested_class = args.class_sheet.strip().lower()
    if requested_class != "all":
        matched_sheet = None
        for sname in wb.sheetnames:
            if sname.strip().lower() == requested_class:
                matched_sheet = sname
                break
        if matched_sheet:
            for sname in list(wb.sheetnames):
                if sname != matched_sheet:
                    del wb[sname]
        else:
            # If sheet name not found verbatim, keep the first sheet and rename it
            first_sheet = wb.sheetnames[0]
            wb[first_sheet].title = args.class_sheet
            for sname in list(wb.sheetnames)[1:]:
                del wb[sname]

    month_titles = {
        "1": "1ST MAN",
        "2": "2ND MAN",
        "3": "3RD MAN",
        "4": "4TH MAN",
        "5": "5TH MAN",
    }

    selected_month = str(args.month).strip().lower()

    # Meta definition for blocks in the 5-month workbook
    # Block 1 (M1): rows 1-23, student rows 8-22
    # Block 2 (M2): rows 24-46, student rows 31-45
    # Block 3 (M3): rows 47-69, student rows 54-68
    # Block 4 (M4): rows 70-92, student rows 77-91
    # Block 5 (M5): rows 93-115, student rows 100-114
    blocks_meta = {
        "1": {"start_row": 8, "banner_cell": "A1", "class_cell": "C4", "teacher_cell": "G4", "period_cell": "C5", "subject_cell": "G5"},
        "2": {"start_row": 31, "banner_cell": "A24", "class_cell": "C27", "teacher_cell": "G27", "period_cell": "C28", "subject_cell": "G28"},
        "3": {"start_row": 54, "banner_cell": "A47", "class_cell": "C50", "teacher_cell": "G50", "period_cell": "C51", "subject_cell": "G51"},
        "4": {"start_row": 77, "banner_cell": "A70", "class_cell": "C73", "teacher_cell": "G73", "period_cell": "C74", "subject_cell": "G74"},
        "5": {"start_row": 100, "banner_cell": "A93", "class_cell": "C96", "teacher_cell": "G96", "period_cell": "C97", "subject_cell": "G97"},
    }

    # Helper to retrieve class data from json
    classes_dict = grades_data.get("classes", {})

    def get_class_data(sheet_name):
        clean_name = sheet_name.strip()
        # Direct lookup
        if clean_name in classes_dict:
            return classes_dict[clean_name]
        # Case-insensitive lookup
        for k, v in classes_dict.items():
            if k.strip().lower() == clean_name.lower():
                return v
        # Fallback to first available class data or empty
        if len(classes_dict) == 1:
            return next(iter(classes_dict.values()))
        return {}

    if selected_month in ["1", "2", "3", "4", "5"]:
        # =========================================================================
        # SINGLE MONTH EXPORT (Month 1, 2, 3, 4, or 5)
        # =========================================================================
        m_title = month_titles[selected_month]

        for sheet_name in wb.sheetnames:
            ws = wb[sheet_name]
            clean_class_name = sheet_name.strip()
            c_data = get_class_data(sheet_name)
            teacher_name = c_data.get("teacher") or args.teacher
            period_str = c_data.get("period") or args.period or f"Bulan Ke-{selected_month}"

            # Month students data
            months_data = c_data.get("months", {})
            students_list = months_data.get(selected_month) or months_data.get(int(selected_month)) or []

            # 1. Update Title Banner (A1:N2)
            ws["A1"] = m_title

            # 2. Update Class, Teacher, Period, Subject
            ws["C4"] = clean_class_name
            ws["G4"] = teacher_name
            ws["C5"] = period_str
            ws["G5"] = args.subject

            # 3. Populate student rows 8 to 22
            for i in range(15):
                row_idx = 8 + i
                ws.cell(row=row_idx, column=1).value = float(i + 1)

                if i < len(students_list):
                    std = students_list[i]
                    std_name = std.get("name", "")
                    feedback = std.get("feedback") or ""

                    ws.cell(row=row_idx, column=2).value = std_name
                    ws.cell(row=row_idx, column=3).value = feedback

                    m1 = std.get("meeting_1", std.get("m1"))
                    m2 = std.get("meeting_2", std.get("m2"))
                    m3 = std.get("meeting_3", std.get("m3"))
                    m4 = std.get("meeting_4", std.get("m4"))

                    if m1 is not None and m1 != "": ws.cell(row=row_idx, column=4).value = float(m1)
                    else: ws.cell(row=row_idx, column=4).value = None

                    if m2 is not None and m2 != "": ws.cell(row=row_idx, column=5).value = float(m2)
                    else: ws.cell(row=row_idx, column=5).value = None

                    if m3 is not None and m3 != "": ws.cell(row=row_idx, column=6).value = float(m3)
                    else: ws.cell(row=row_idx, column=6).value = None

                    if m4 is not None and m4 != "": ws.cell(row=row_idx, column=7).value = float(m4)
                    else: ws.cell(row=row_idx, column=7).value = None

                    fluency = std.get("fluency")
                    grammar = std.get("grammar")
                    pronunciation = std.get("pronunciation")
                    vocabulary = std.get("vocabulary")

                    if fluency is not None and fluency != "": ws.cell(row=row_idx, column=9).value = float(fluency)
                    else: ws.cell(row=row_idx, column=9).value = None

                    if grammar is not None and grammar != "": ws.cell(row=row_idx, column=10).value = float(grammar)
                    else: ws.cell(row=row_idx, column=10).value = None

                    if pronunciation is not None and pronunciation != "": ws.cell(row=row_idx, column=11).value = float(pronunciation)
                    else: ws.cell(row=row_idx, column=11).value = None

                    if vocabulary is not None and vocabulary != "": ws.cell(row=row_idx, column=12).value = float(vocabulary)
                    else: ws.cell(row=row_idx, column=12).value = None
                else:
                    ws.cell(row=row_idx, column=2).value = None
                    ws.cell(row=row_idx, column=3).value = None
                    ws.cell(row=row_idx, column=4).value = None
                    ws.cell(row=row_idx, column=5).value = None
                    ws.cell(row=row_idx, column=6).value = None
                    ws.cell(row=row_idx, column=7).value = None
                    ws.cell(row=row_idx, column=9).value = None
                    ws.cell(row=row_idx, column=10).value = None
                    ws.cell(row=row_idx, column=11).value = None
                    ws.cell(row=row_idx, column=12).value = None

                # Keep standard calculation formulas
                ws.cell(row=row_idx, column=8).value = f"=(D{row_idx}+E{row_idx}+F{row_idx}+G{row_idx})/4"
                ws.cell(row=row_idx, column=13).value = f"=(I{row_idx}+J{row_idx}+K{row_idx}+L{row_idx})/4"
                ws.cell(row=row_idx, column=14).value = f"=((H{row_idx}+M{row_idx})/2)"

            # 4. Remove merged cells with min_row >= 23
            to_remove = [m for m in list(ws.merged_cells.ranges) if m.min_row >= 23]
            for m in to_remove:
                ws.merged_cells.ranges.remove(m)

            # 5. Delete rows 23 onward
            if ws.max_row > 22:
                ws.delete_rows(23, ws.max_row - 22 + 5)

    else:
        # =========================================================================
        # ALL MONTHS EXPORT (Bulan 1 sampai Bulan 5)
        # =========================================================================
        for sheet_name in wb.sheetnames:
            ws = wb[sheet_name]
            clean_class_name = sheet_name.strip()
            c_data = get_class_data(sheet_name)
            teacher_name = c_data.get("teacher") or args.teacher
            period_str = c_data.get("period") or args.period or "Bulan 1 - Bulan 5"
            months_data = c_data.get("months", {})

            # 1. Clone Block 4 into Block 5 (5TH MAN)
            clone_block(ws, 70, 93, 23)
            ws["A93"] = "5TH MAN"

            # 2. Update Header Information for All 5 Blocks
            for m_key in ["1", "2", "3", "4", "5"]:
                meta = blocks_meta[m_key]
                ws[meta["class_cell"]] = clean_class_name
                ws[meta["teacher_cell"]] = teacher_name
                ws[meta["subject_cell"]] = args.subject
                ws[meta["period_cell"]] = f"Bulan Ke-{m_key}"

            # 3. Populate student rows for all 5 blocks
            # Find the master student list (using Month 1, or any month that has students)
            all_students_master = []
            for m_key in ["1", "2", "3", "4", "5"]:
                m_stds = months_data.get(m_key) or months_data.get(int(m_key)) or []
                if len(m_stds) > len(all_students_master):
                    all_students_master = m_stds

            for i in range(15):
                has_student = i < len(all_students_master)
                master_name = all_students_master[i].get("name", "") if has_student else ""

                for m_key in ["1", "2", "3", "4", "5"]:
                    meta = blocks_meta[m_key]
                    curr_row = meta["start_row"] + i
                    m_stds = months_data.get(m_key) or months_data.get(int(m_key)) or []
                    std = m_stds[i] if i < len(m_stds) else (all_students_master[i] if has_student else None)

                    ws.cell(row=curr_row, column=1).value = float(i + 1)

                    if has_student:
                        if m_key == "1":
                            ws.cell(row=curr_row, column=2).value = master_name
                        else:
                            # Use reference formula to Month 1 name cell (=B8, =B9, etc.)
                            month_1_row = blocks_meta["1"]["start_row"] + i
                            ws.cell(row=curr_row, column=2).value = f"=B{month_1_row}"

                        feedback = std.get("feedback") if std else ""
                        ws.cell(row=curr_row, column=3).value = feedback or ""

                        m1 = std.get("meeting_1", std.get("m1")) if std else None
                        m2 = std.get("meeting_2", std.get("m2")) if std else None
                        m3 = std.get("meeting_3", std.get("m3")) if std else None
                        m4 = std.get("meeting_4", std.get("m4")) if std else None

                        if m1 is not None and m1 != "": ws.cell(row=curr_row, column=4).value = float(m1)
                        else: ws.cell(row=curr_row, column=4).value = None

                        if m2 is not None and m2 != "": ws.cell(row=curr_row, column=5).value = float(m2)
                        else: ws.cell(row=curr_row, column=5).value = None

                        if m3 is not None and m3 != "": ws.cell(row=curr_row, column=6).value = float(m3)
                        else: ws.cell(row=curr_row, column=6).value = None

                        if m4 is not None and m4 != "": ws.cell(row=curr_row, column=7).value = float(m4)
                        else: ws.cell(row=curr_row, column=7).value = None

                        fluency = std.get("fluency") if std else None
                        grammar = std.get("grammar") if std else None
                        pronunciation = std.get("pronunciation") if std else None
                        vocabulary = std.get("vocabulary") if std else None

                        if fluency is not None and fluency != "": ws.cell(row=curr_row, column=9).value = float(fluency)
                        else: ws.cell(row=curr_row, column=9).value = None

                        if grammar is not None and grammar != "": ws.cell(row=curr_row, column=10).value = float(grammar)
                        else: ws.cell(row=curr_row, column=10).value = None

                        if pronunciation is not None and pronunciation != "": ws.cell(row=curr_row, column=11).value = float(pronunciation)
                        else: ws.cell(row=curr_row, column=11).value = None

                        if vocabulary is not None and vocabulary != "": ws.cell(row=curr_row, column=12).value = float(vocabulary)
                        else: ws.cell(row=curr_row, column=12).value = None
                    else:
                        ws.cell(row=curr_row, column=2).value = None
                        ws.cell(row=curr_row, column=3).value = None
                        ws.cell(row=curr_row, column=4).value = None
                        ws.cell(row=curr_row, column=5).value = None
                        ws.cell(row=curr_row, column=6).value = None
                        ws.cell(row=curr_row, column=7).value = None
                        ws.cell(row=curr_row, column=9).value = None
                        ws.cell(row=curr_row, column=10).value = None
                        ws.cell(row=curr_row, column=11).value = None
                        ws.cell(row=curr_row, column=12).value = None

                    # Calculation formulas for this row
                    ws.cell(row=curr_row, column=8).value = f"=(D{curr_row}+E{curr_row}+F{curr_row}+G{curr_row})/4"
                    ws.cell(row=curr_row, column=13).value = f"=(I{curr_row}+J{curr_row}+K{curr_row}+L{curr_row})/4"
                    ws.cell(row=curr_row, column=14).value = f"=((H{curr_row}+M{curr_row})/2)"

    os.makedirs(os.path.dirname(os.path.abspath(args.output)), exist_ok=True)
    wb.save(args.output)

    print(json.dumps({
        "success": True,
        "output": args.output,
        "sheets": wb.sheetnames,
        "month": selected_month
    }))

if __name__ == "__main__":
    main()
