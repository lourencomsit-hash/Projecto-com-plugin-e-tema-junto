import zipfile, os, io
from PIL import Image

theme_dir = os.path.normpath("c:/Projetos/Breeze Safaris/Website para WP/breeze-claude-theme")
photos_dir = os.path.join(theme_dir, "assets", "media", "photos")
out_zip = "c:/Projetos/Breeze Safaris/zips/breeze-claude-theme-v5.0.zip"

# Compress JPGs in-memory (keyed by normpath)
compressed = {}
total_before = 0
total_after = 0

for fname in os.listdir(photos_dir):
    ext = fname.lower().rsplit('.', 1)[-1]
    if ext not in ('jpg', 'jpeg'):
        continue
    fpath = os.path.normpath(os.path.join(photos_dir, fname))
    before = os.path.getsize(fpath)
    total_before += before

    img = Image.open(fpath)
    w, h = img.size
    max_dim = 2000
    if w > max_dim or h > max_dim:
        ratio = min(max_dim / w, max_dim / h)
        img = img.resize((int(w * ratio), int(h * ratio)), Image.LANCZOS)
    if img.mode in ('RGBA', 'P'):
        img = img.convert('RGB')

    buf = io.BytesIO()
    img.save(buf, 'JPEG', quality=82, optimize=True, progressive=True)
    data = buf.getvalue()
    compressed[fpath] = data
    total_after += len(data)
    print(f"  {fname}: {before//1024}KB -> {len(data)//1024}KB")

print(f"\nFotos: {total_before//1024//1024}MB -> {total_after//1024//1024}MB")

# Build zip
parent_dir = os.path.dirname(theme_dir)
with zipfile.ZipFile(out_zip, 'w', zipfile.ZIP_DEFLATED, compresslevel=6) as zf:
    for root, dirs, files in os.walk(theme_dir):
        dirs[:] = [d for d in dirs if d not in ('__pycache__', '.git')]
        for file in [f for f in files if not f.endswith('.bak')]:
            abs_path = os.path.normpath(os.path.join(root, file))
            rel = os.path.relpath(abs_path, parent_dir)
            arc = rel.replace(os.sep, '/')
            if abs_path in compressed:
                zf.writestr(zipfile.ZipInfo(arc), compressed[abs_path])
            else:
                zf.write(abs_path, arc)

size = os.path.getsize(out_zip)
print(f"\nZIP: {out_zip}")
print(f"Tamanho: {size/1024/1024:.1f} MB")

with zipfile.ZipFile(out_zip) as zf:
    styles = [n for n in zf.namelist() if n.endswith('style.css')]
    print(f"style.css: {styles}")
