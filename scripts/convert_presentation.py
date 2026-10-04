import sys
import os
import json
import re
import zipfile
import xml.etree.ElementTree as ET
from PIL import Image, ImageDraw, ImageFont

def natural_sort_key(s):
    return [int(text) if text.isdigit() else text.lower() for text in re.split(r'(\d+)', s)]

def convert_pptx_fallback(input_path, output_dir, output_pdf_path):
    """
    Pure Python fallback for PPTX files when PowerPoint COM is not available or fails.
    Extracts text, titles, and media from PPTX zip structure and renders high-res slide images.
    """
    os.makedirs(output_dir, exist_ok=True)
    try:
        z = zipfile.ZipFile(input_path, 'r')
        
        # 1. Identify all slide XML files
        slide_map = {}
        for name in z.namelist():
            m = re.match(r'^ppt/slides/slide(\d+)\.xml$', name)
            if m:
                slide_map[int(m.group(1))] = name
                
        sorted_slide_keys = sorted(slide_map.keys())
        if not sorted_slide_keys:
            return {"success": False, "error": "No slides found in PPTX package"}

        # 2. Extract media images
        media_files = {}
        for name in z.namelist():
            if name.startswith('ppt/media/'):
                media_files[name] = z.read(name)

        rendered_slides = []
        pil_images = []

        # Color palette for fallback slides
        bg_colors = [
            (248, 250, 252), # Slate light
            (241, 245, 249), # Cool gray
            (238, 242, 255), # Indigo light
        ]

        width = 1280
        height = 720

        for idx, slide_num in enumerate(sorted_slide_keys):
            xml_name = slide_map[slide_num]
            xml_content = z.read(xml_name)
            
            # Clean namespaces for easy tag parsing
            clean_xml = re.sub(r'</?\w+:', lambda m: '<' if not m.group(0).startswith('</') else '</', xml_content.decode('utf-8', errors='ignore'))
            
            # Parse text lines
            lines = []
            try:
                root = ET.fromstring(clean_xml)
                for p in root.findall('.//p'):
                    txt = ''.join([t.text for t in p.findall('.//t') if t.text]).strip()
                    if txt:
                        lines.append(txt)
            except Exception:
                # Regex fallback
                raw_texts = re.findall(r'<[^>]*t[^>]*>([^<]+)</[^>]*t>', xml_content.decode('utf-8', errors='ignore'))
                lines = [t.strip() for t in raw_texts if t.strip()]

            title = lines[0] if lines else f"Slide {idx + 1}"
            body_lines = lines[1:] if len(lines) > 1 else []

            # Create slide image
            bg_color = bg_colors[idx % len(bg_colors)]
            img = Image.new('RGB', (width, height), bg_color)
            draw = ImageDraw.Draw(img)

            # Header band
            draw.rectangle([(0, 0), (width, 90)], fill=(30, 27, 75)) # Deep Indigo
            
            # Try to load a nice font, fallback to default
            try:
                font_title = ImageFont.truetype("arial.ttf", 36)
                font_sub = ImageFont.truetype("arial.ttf", 22)
                font_body = ImageFont.truetype("arial.ttf", 20)
                font_footer = ImageFont.truetype("arial.ttf", 16)
            except Exception:
                font_title = ImageFont.load_default()
                font_sub = ImageFont.load_default()
                font_body = ImageFont.load_default()
                font_footer = ImageFont.load_default()

            # Draw header title
            draw.text((40, 26), f"SLIDE {idx + 1} / {len(sorted_slide_keys)}", fill=(147, 197, 253), font=font_sub)
            
            # Draw slide title
            display_title = title if len(title) <= 90 else title[:87] + "..."
            draw.text((40, 120), display_title, fill=(15, 23, 42), font=font_title)
            draw.line([(40, 175), (width - 40, 175)], fill=(203, 213, 225), width=2)

            # Draw content lines
            y = 200
            content_width = width - 80
            
            # Check for embedded media matching this slide
            rels_name = f"ppt/slides/_rels/slide{slide_num}.xml.rels"
            slide_images_data = []
            if rels_name in z.namelist():
                rels_content = z.read(rels_name).decode('utf-8', errors='ignore')
                targets = re.findall(r'Target="([^"]+)"', rels_content)
                for t in targets:
                    norm = 'ppt/' + t.replace('../', '').lstrip('/')
                    if norm in media_files:
                        slide_images_data.append(media_files[norm])

            # If embedded image exists, display it on the right side
            if slide_images_data:
                content_width = 700
                try:
                    import io
                    emb_img = Image.open(io.BytesIO(slide_images_data[0]))
                    emb_img.thumbnail((480, 420))
                    img.paste(emb_img, (740, 200))
                except Exception:
                    pass

            for line in body_lines[:12]:
                wrapped_text = line if len(line) <= 75 else line[:72] + "..."
                draw.text((50, y), f"•  {wrapped_text}", fill=(51, 65, 85), font=font_body)
                y += 38
                if y > height - 100:
                    break

            # Footer
            draw.rectangle([(0, height - 40), (width, height)], fill=(15, 23, 42))
            draw.text((40, height - 30), "Musashi Learning System • Presentation Viewer", fill=(148, 163, 184), font=font_footer)
            draw.text((width - 120, height - 30), f"Halaman {idx + 1}", fill=(148, 163, 184), font=font_footer)

            # Save slide image
            fname = f"Slide{idx + 1}.jpg"
            out_file = os.path.join(output_dir, fname)
            img.save(out_file, quality=90)
            rendered_slides.append(fname)
            pil_images.append(img.convert('RGB'))

        z.close()

        # Save combined PDF
        pdf_created = False
        if pil_images and output_pdf_path:
            try:
                os.makedirs(os.path.dirname(os.path.abspath(output_pdf_path)), exist_ok=True)
                pil_images[0].save(output_pdf_path, save_all=True, append_images=pil_images[1:])
                pdf_created = os.path.exists(output_pdf_path)
            except Exception as pe:
                print(f"Fallback PDF generation error: {pe}", file=sys.stderr)

        return {
            "success": True,
            "method": "pure_python_zip_fallback",
            "total_slides": len(rendered_slides),
            "slides": rendered_slides,
            "pdf_created": pdf_created
        }
    except Exception as e:
        print(f"Fallback converter error: {e}", file=sys.stderr)
        return {"success": False, "error": str(e)}

def convert_pptx(input_path, output_dir, output_pdf_path):
    os.makedirs(output_dir, exist_ok=True)
    abs_input = os.path.abspath(input_path)
    abs_out_dir = os.path.abspath(output_dir)
    abs_out_pdf = os.path.abspath(output_pdf_path)
    
    # 1. Try Native PowerPoint COM first (Highest quality native rendering)
    try:
        import win32com.client
        app = win32com.client.Dispatch("PowerPoint.Application")
        app.DisplayAlerts = 1  # ppAlertsNone = 1: Suppress all alerts & dialog popups!
        
        try:
            # Open ReadOnly=True, Untitled=False, WithWindow=False
            pres = app.Presentations.Open(abs_input, True, False, False)
            total_slides = pres.Slides.Count
            
            # Export all slides as JPG/PNG (17 = ppSaveAsJPG)
            pres.SaveAs(abs_out_dir, 17)
            
            # Export as PDF (32 = ppSaveAsPDF)
            try:
                pres.SaveAs(abs_out_pdf, 32)
            except Exception as pe:
                print(f"PDF save warning: {pe}", file=sys.stderr)
                
            pres.Close()
            
            # Gather exported slide image files
            files = [f for f in os.listdir(output_dir) if f.lower().endswith(('.jpg', '.jpeg', '.png'))]
            files.sort(key=natural_sort_key)
            
            if files:
                return {
                    "success": True,
                    "method": "powerpoint_com",
                    "total_slides": len(files),
                    "slides": files,
                    "pdf_created": os.path.exists(abs_out_pdf)
                }
        finally:
            try:
                if app.Presentations.Count == 0:
                    app.Quit()
            except Exception:
                pass
    except Exception as e:
        print(f"PowerPoint COM failed or unavailable: {e}. Falling back to Pure Python parser...", file=sys.stderr)

    # 2. Pure Python fallback (Never fails for valid PPTX)
    return convert_pptx_fallback(input_path, output_dir, output_pdf_path)

def convert_pdf(input_path, output_dir, output_pdf_path=None):
    os.makedirs(output_dir, exist_ok=True)
    abs_input = os.path.abspath(input_path)
    
    # Try PyMuPDF (fitz)
    try:
        import fitz  # PyMuPDF
        doc = fitz.open(abs_input)
        total_pages = len(doc)
        slide_files = []
        for i, page in enumerate(doc):
            pix = page.get_pixmap(dpi=150)
            fname = f"Slide{i+1}.jpg"
            fpath = os.path.join(output_dir, fname)
            pix.save(fpath)
            slide_files.append(fname)
        doc.close()
        
        # If output_pdf_path is given and different, copy original PDF
        if output_pdf_path and os.path.abspath(output_pdf_path) != abs_input:
            import shutil
            os.makedirs(os.path.dirname(os.path.abspath(output_pdf_path)), exist_ok=True)
            shutil.copyfile(abs_input, output_pdf_path)

        return {
            "success": True,
            "method": "pymupdf",
            "total_slides": total_pages,
            "slides": slide_files,
            "pdf_created": True
        }
    except Exception as e:
        print(f"PyMuPDF error: {e}", file=sys.stderr)

    # Fallback with pypdf + pdf2image if available
    try:
        from pdf2image import convert_from_path
        images = convert_from_path(abs_input, dpi=150)
        slide_files = []
        for i, img in enumerate(images):
            fname = f"Slide{i+1}.jpg"
            fpath = os.path.join(output_dir, fname)
            img.save(fpath, "JPEG")
            slide_files.append(fname)
        return {
            "success": True,
            "method": "pdf2image",
            "total_slides": len(slide_files),
            "slides": slide_files,
            "pdf_created": True
        }
    except Exception as e2:
        return {"success": False, "error": f"PDF conversion failed: {e2}"}

def detect_format(input_path, hinted_ext=None):
    if hinted_ext:
        clean_hint = hinted_ext.strip().lower()
        if not clean_hint.startswith('.'):
            clean_hint = '.' + clean_hint
        if clean_hint in ['.pptx', '.ppt', '.pps', '.ppsx', '.pdf']:
            return clean_hint

    ext = os.path.splitext(input_path)[1].lower()
    if ext in ['.pptx', '.ppt', '.pps', '.ppsx', '.pdf']:
        return ext

    # Inspect magic bytes
    try:
        with open(input_path, 'rb') as f:
            header = f.read(16)
            if header.startswith(b'PK\x03\x04'):
                return '.pptx'
            elif header.startswith(b'%PDF'):
                return '.pdf'
            elif header.startswith(b'\xd0\xcf\x11\xe0'):
                return '.ppt'
    except Exception:
        pass

    return ext

def main():
    if len(sys.argv) < 3:
        print(json.dumps({"success": False, "error": "Missing arguments. Usage: python convert_presentation.py <input_path> <output_dir> [output_pdf_path] [hinted_ext]"}))
        sys.exit(1)
        
    input_path = sys.argv[1]
    output_dir = sys.argv[2]
    output_pdf_path = sys.argv[3] if len(sys.argv) > 3 and sys.argv[3] != "" else os.path.join(output_dir, "presentation.pdf")
    hinted_ext = sys.argv[4] if len(sys.argv) > 4 else None
    
    if not os.path.exists(input_path):
        print(json.dumps({"success": False, "error": f"Input file not found: {input_path}"}))
        sys.exit(1)
        
    ext = detect_format(input_path, hinted_ext)
    
    # If the input file does not end with the detected extension (e.g. it was saved as .bin),
    # create a temporary symlink/copy with the correct extension so PowerPoint COM will open it
    actual_ext = os.path.splitext(input_path)[1].lower()
    temp_file = None
    file_to_convert = input_path
    
    if actual_ext != ext and ext in ['.pptx', '.ppt', '.pps', '.ppsx', '.pdf']:
        import shutil
        import tempfile
        temp_dir = tempfile.gettempdir()
        temp_file = os.path.join(temp_dir, f"conv_{os.path.basename(input_path)}{ext}")
        shutil.copyfile(input_path, temp_file)
        file_to_convert = temp_file

    try:
        if ext in ['.pptx', '.ppt', '.pps', '.ppsx']:
            result = convert_pptx(file_to_convert, output_dir, output_pdf_path)
        elif ext in ['.pdf']:
            result = convert_pdf(file_to_convert, output_dir, output_pdf_path)
        else:
            result = {"success": False, "error": f"Unsupported format: {ext}"}
            
        print(json.dumps(result))
    finally:
        if temp_file and os.path.exists(temp_file):
            try:
                os.remove(temp_file)
            except Exception:
                pass

if __name__ == '__main__':
    main()

