"""
Generate MENRO Hostinger Deployment Guide as a .docx Word document.
Run: python make_deploy_guide.py
Output: MENRO_Hostinger_Deployment_Guide.docx
"""

from docx import Document
from docx.shared import Pt, RGBColor, Inches, Cm
from docx.enum.text import WD_ALIGN_PARAGRAPH, WD_LINE_SPACING
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_ALIGN_VERTICAL
from docx.oxml.ns import qn
from docx.oxml import OxmlElement
import copy

doc = Document()

# ── Page margins ──────────────────────────────────────────────────────────────
for section in doc.sections:
    section.top_margin    = Cm(2.0)
    section.bottom_margin = Cm(2.0)
    section.left_margin   = Cm(2.5)
    section.right_margin  = Cm(2.5)

# ── Helper: shade a table cell ────────────────────────────────────────────────
def shade_cell(cell, hex_color):
    tc = cell._tc
    tcPr = tc.get_or_add_tcPr()
    shd = OxmlElement('w:shd')
    shd.set(qn('w:val'),   'clear')
    shd.set(qn('w:color'), 'auto')
    shd.set(qn('w:fill'),  hex_color)
    tcPr.append(shd)

# ── Helper: set table borders ─────────────────────────────────────────────────
def set_table_borders(table, color='4CAF50', size='4'):
    tbl  = table._tbl
    tblPr = tbl.find(qn('w:tblPr'))
    if tblPr is None:
        tblPr = OxmlElement('w:tblPr')
        tbl.insert(0, tblPr)
    tblBorders = OxmlElement('w:tblBorders')
    for border_name in ('top','left','bottom','right','insideH','insideV'):
        b = OxmlElement(f'w:{border_name}')
        b.set(qn('w:val'),   'single')
        b.set(qn('w:sz'),    size)
        b.set(qn('w:space'), '0')
        b.set(qn('w:color'), color)
        tblBorders.append(b)
    tblPr.append(tblBorders)

# ── Helper: add a code block paragraph ───────────────────────────────────────
def add_code(doc, text):
    p = doc.add_paragraph()
    p.paragraph_format.space_before = Pt(2)
    p.paragraph_format.space_after  = Pt(2)
    pPr = p._p.get_or_add_pPr()
    shd = OxmlElement('w:shd')
    shd.set(qn('w:val'),   'clear')
    shd.set(qn('w:color'), 'auto')
    shd.set(qn('w:fill'),  '1E1E1E')
    pPr.append(shd)
    # left indent
    ind = OxmlElement('w:ind')
    ind.set(qn('w:left'), '360')
    pPr.append(ind)
    run = p.add_run(text)
    run.font.name  = 'Courier New'
    run.font.size  = Pt(9)
    run.font.color.rgb = RGBColor(0x9C, 0xD3, 0x77)  # green-ish
    run.font.bold  = False
    return p

def add_code_comment(doc, text):
    p = doc.add_paragraph()
    p.paragraph_format.space_before = Pt(0)
    p.paragraph_format.space_after  = Pt(2)
    pPr = p._p.get_or_add_pPr()
    shd = OxmlElement('w:shd')
    shd.set(qn('w:val'),   'clear')
    shd.set(qn('w:color'), 'auto')
    shd.set(qn('w:fill'),  '1E1E1E')
    pPr.append(shd)
    ind = OxmlElement('w:ind')
    ind.set(qn('w:left'), '360')
    pPr.append(ind)
    run = p.add_run(text)
    run.font.name  = 'Courier New'
    run.font.size  = Pt(9)
    run.font.color.rgb = RGBColor(0x6A, 0x99, 0x55)  # comment gray-green
    run.font.italic = True
    return p

# ── Helper: tip/note box ──────────────────────────────────────────────────────
def add_tip(doc, label, text, bg='FFF3CD', label_color='856404'):
    p = doc.add_paragraph()
    p.paragraph_format.space_before = Pt(4)
    p.paragraph_format.space_after  = Pt(6)
    pPr = p._p.get_or_add_pPr()
    shd = OxmlElement('w:shd')
    shd.set(qn('w:val'),   'clear')
    shd.set(qn('w:color'), 'auto')
    shd.set(qn('w:fill'),  bg)
    pPr.append(shd)
    ind = OxmlElement('w:ind')
    ind.set(qn('w:left'),  '300')
    ind.set(qn('w:right'), '300')
    pPr.append(ind)
    r1 = p.add_run(f'{label}  ')
    r1.font.bold = True
    r1.font.size = Pt(9.5)
    r1.font.color.rgb = RGBColor.from_string(label_color)
    r2 = p.add_run(text)
    r2.font.size = Pt(9.5)
    return p

# ── Helper: step header ───────────────────────────────────────────────────────
def add_step(doc, number, title):
    p = doc.add_paragraph()
    p.paragraph_format.space_before = Pt(14)
    p.paragraph_format.space_after  = Pt(4)
    pPr = p._p.get_or_add_pPr()
    shd = OxmlElement('w:shd')
    shd.set(qn('w:val'),   'clear')
    shd.set(qn('w:color'), 'auto')
    shd.set(qn('w:fill'),  '2E7D32')
    pPr.append(shd)
    ind = OxmlElement('w:ind')
    ind.set(qn('w:left'), '0')
    pPr.append(ind)
    run = p.add_run(f'  STEP {number}   {title}')
    run.font.bold  = True
    run.font.size  = Pt(13)
    run.font.color.rgb = RGBColor(0xFF, 0xFF, 0xFF)
    run.font.name  = 'Calibri'
    return p

def add_body(doc, text, bold=False, italic=False, size=10.5):
    p = doc.add_paragraph()
    p.paragraph_format.space_before = Pt(1)
    p.paragraph_format.space_after  = Pt(3)
    run = p.add_run(text)
    run.font.size   = Pt(size)
    run.font.bold   = bold
    run.font.italic = italic
    return p

def add_bullet(doc, text, level=0):
    p = doc.add_paragraph(style='List Bullet')
    p.paragraph_format.space_before = Pt(1)
    p.paragraph_format.space_after  = Pt(2)
    p.paragraph_format.left_indent  = Inches(0.25 * (level + 1))
    run = p.add_run(text)
    run.font.size = Pt(10)
    return p

def add_num(doc, text, level=0):
    p = doc.add_paragraph(style='List Number')
    p.paragraph_format.space_before = Pt(1)
    p.paragraph_format.space_after  = Pt(2)
    p.paragraph_format.left_indent  = Inches(0.25 * (level + 1))
    run = p.add_run(text)
    run.font.size = Pt(10)
    return p

# ═══════════════════════════════════════════════════════════════════════════════
#  COVER PAGE
# ═══════════════════════════════════════════════════════════════════════════════
# Green banner
p = doc.add_paragraph()
p.alignment = WD_ALIGN_PARAGRAPH.CENTER
p.paragraph_format.space_before = Pt(40)
p.paragraph_format.space_after  = Pt(0)
pPr = p._p.get_or_add_pPr()
shd = OxmlElement('w:shd')
shd.set(qn('w:val'),   'clear')
shd.set(qn('w:color'), 'auto')
shd.set(qn('w:fill'),  '1B5E20')
pPr.append(shd)
run = p.add_run('  MENRO  ')
run.font.size  = Pt(42)
run.font.bold  = True
run.font.color.rgb = RGBColor(0xFF, 0xFF, 0xFF)
run.font.name  = 'Calibri'

p2 = doc.add_paragraph()
p2.alignment = WD_ALIGN_PARAGRAPH.CENTER
p2.paragraph_format.space_before = Pt(0)
p2.paragraph_format.space_after  = Pt(0)
pPr2 = p2._p.get_or_add_pPr()
shd2 = OxmlElement('w:shd')
shd2.set(qn('w:val'),   'clear')
shd2.set(qn('w:color'), 'auto')
shd2.set(qn('w:fill'),  '2E7D32')
pPr2.append(shd2)
run2 = p2.add_run('  Waste Management Information System  ')
run2.font.size  = Pt(14)
run2.font.color.rgb = RGBColor(0xC8, 0xE6, 0xC9)
run2.font.name  = 'Calibri'

doc.add_paragraph()

p3 = doc.add_paragraph()
p3.alignment = WD_ALIGN_PARAGRAPH.CENTER
p3.paragraph_format.space_before = Pt(20)
p3.paragraph_format.space_after  = Pt(4)
r3 = p3.add_run('Hostinger VPS Deployment Guide')
r3.font.size  = Pt(22)
r3.font.bold  = True
r3.font.color.rgb = RGBColor(0x1B, 0x5E, 0x20)

p4 = doc.add_paragraph()
p4.alignment = WD_ALIGN_PARAGRAPH.CENTER
p4.paragraph_format.space_before = Pt(0)
p4.paragraph_format.space_after  = Pt(6)
r4 = p4.add_run('Step-by-Step Setup on Ubuntu 22.04 LTS')
r4.font.size  = Pt(13)
r4.font.color.rgb = RGBColor(0x55, 0x55, 0x55)

doc.add_paragraph()

# Info box
tbl = doc.add_table(rows=1, cols=2)
tbl.alignment = WD_TABLE_ALIGNMENT.CENTER
tbl.columns[0].width = Cm(7)
tbl.columns[1].width = Cm(7)
set_table_borders(tbl, 'BDBDBD', '4')
row = tbl.rows[0]
for cell, label, val in [
    (row.cells[0], 'Platform', 'Hostinger KVM VPS'),
    (row.cells[1], 'OS',       'Ubuntu 22.04 LTS'),
]:
    shade_cell(cell, 'F1F8E9')
    cell.vertical_alignment = WD_ALIGN_VERTICAL.CENTER
    p = cell.paragraphs[0]
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p.paragraph_format.space_before = Pt(6)
    p.paragraph_format.space_after  = Pt(6)
    r = p.add_run(f'{label}\n')
    r.font.bold = True
    r.font.size = Pt(10)
    r.font.color.rgb = RGBColor(0x2E, 0x7D, 0x32)
    r2 = p.add_run(val)
    r2.font.size = Pt(10)

tbl2 = doc.add_table(rows=1, cols=2)
tbl2.alignment = WD_TABLE_ALIGNMENT.CENTER
tbl2.columns[0].width = Cm(7)
tbl2.columns[1].width = Cm(7)
set_table_borders(tbl2, 'BDBDBD', '4')
row2 = tbl2.rows[0]
for cell, label, val in [
    (row2.cells[0], 'Stack',   'PHP 8.3 · Laravel 9 · MySQL 8'),
    (row2.cells[1], 'Web Server', 'Nginx + PHP-FPM'),
]:
    shade_cell(cell, 'F1F8E9')
    cell.vertical_alignment = WD_ALIGN_VERTICAL.CENTER
    p = cell.paragraphs[0]
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p.paragraph_format.space_before = Pt(6)
    p.paragraph_format.space_after  = Pt(6)
    r = p.add_run(f'{label}\n')
    r.font.bold = True
    r.font.size = Pt(10)
    r.font.color.rgb = RGBColor(0x2E, 0x7D, 0x32)
    r2 = p.add_run(val)
    r2.font.size = Pt(10)

doc.add_paragraph()

# Date line
p5 = doc.add_paragraph()
p5.alignment = WD_ALIGN_PARAGRAPH.CENTER
r5 = p5.add_run('Prepared: May 2026  ·  Version 1.0')
r5.font.size  = Pt(9)
r5.font.color.rgb = RGBColor(0x99, 0x99, 0x99)

doc.add_page_break()

# ═══════════════════════════════════════════════════════════════════════════════
#  TABLE OF CONTENTS (manual)
# ═══════════════════════════════════════════════════════════════════════════════
h = doc.add_paragraph()
r = h.add_run('Table of Contents')
r.font.size  = Pt(16)
r.font.bold  = True
r.font.color.rgb = RGBColor(0x1B, 0x5E, 0x20)
h.paragraph_format.space_after = Pt(8)

toc_items = [
    ('Prerequisites',                                    '3'),
    ('Step 1  —  Point Your Domain to the VPS',          '3'),
    ('Step 2  —  SSH into the VPS',                      '4'),
    ('Step 3  —  Run the Automated Deploy Script',       '4'),
    ('Step 4  —  Upload Your Application Files',         '5'),
    ('Step 5  —  Configure the Environment File (.env)', '6'),
    ('Step 6  —  Finish Laravel Setup',                  '7'),
    ('Step 7  —  Enable HTTPS (Free SSL)',                '8'),
    ('Step 8  —  Verify the Deployment',                 '9'),
    ('Updating the App (Re-deploy)',                     '9'),
    ('Troubleshooting',                                  '10'),
]
tbl_toc = doc.add_table(rows=len(toc_items), cols=2)
tbl_toc.alignment = WD_TABLE_ALIGNMENT.LEFT
tbl_toc.columns[0].width = Cm(13)
tbl_toc.columns[1].width = Cm(1.5)
for i, (title, page) in enumerate(toc_items):
    bg = 'F9FBE7' if i % 2 == 0 else 'FFFFFF'
    c0 = tbl_toc.rows[i].cells[0]
    c1 = tbl_toc.rows[i].cells[1]
    shade_cell(c0, bg); shade_cell(c1, bg)
    p0 = c0.paragraphs[0]
    p0.paragraph_format.space_before = Pt(3)
    p0.paragraph_format.space_after  = Pt(3)
    r0 = p0.add_run(title)
    r0.font.size = Pt(10)
    if i == 0:
        r0.font.bold = True
    p1 = c1.paragraphs[0]
    p1.alignment = WD_ALIGN_PARAGRAPH.RIGHT
    p1.paragraph_format.space_before = Pt(3)
    p1.paragraph_format.space_after  = Pt(3)
    r1 = p1.add_run(page)
    r1.font.size = Pt(10)
    r1.font.color.rgb = RGBColor(0x2E, 0x7D, 0x32)
set_table_borders(tbl_toc, 'E0E0E0', '4')

doc.add_page_break()

# ═══════════════════════════════════════════════════════════════════════════════
#  PREREQUISITES
# ═══════════════════════════════════════════════════════════════════════════════
ph = doc.add_paragraph()
r = ph.add_run('Prerequisites')
r.font.size  = Pt(16)
r.font.bold  = True
r.font.color.rgb = RGBColor(0x1B, 0x5E, 0x20)
ph.paragraph_format.space_after = Pt(6)

add_body(doc, 'Before you begin, make sure you have the following ready:')

prereqs = [
    ('Hostinger VPS Plan',  'KVM 1 or higher (KVM 2 recommended for production)'),
    ('Operating System',    'Ubuntu 22.04 LTS — select this when setting up the VPS in Hostinger'),
    ('Root SSH Access',     'You must be able to log in as root via SSH'),
    ('Domain Name',         'Any domain or subdomain pointed to your VPS IP address'),
    ('Application Files',   'The MENRO project files (GitHub repo or a local copy as a ZIP)'),
    ('SSH Client',          'Windows: use PuTTY or Windows Terminal  ·  Mac/Linux: built-in terminal'),
    ('SFTP Client',         'FileZilla (free) — for uploading files if not using Git'),
]
tbl_pre = doc.add_table(rows=1 + len(prereqs), cols=2)
tbl_pre.alignment = WD_TABLE_ALIGNMENT.LEFT
tbl_pre.columns[0].width = Cm(5)
tbl_pre.columns[1].width = Cm(10)
set_table_borders(tbl_pre, '4CAF50', '4')

# header row
hc0 = tbl_pre.rows[0].cells[0]; hc1 = tbl_pre.rows[0].cells[1]
for c, t in [(hc0, 'Requirement'), (hc1, 'Details')]:
    shade_cell(c, '2E7D32')
    p = c.paragraphs[0]
    p.paragraph_format.space_before = Pt(4)
    p.paragraph_format.space_after  = Pt(4)
    r = p.add_run(t)
    r.font.bold = True; r.font.size = Pt(10.5)
    r.font.color.rgb = RGBColor(0xFF, 0xFF, 0xFF)

for i, (req, det) in enumerate(prereqs):
    bg = 'F1F8E9' if i % 2 == 0 else 'FFFFFF'
    c0 = tbl_pre.rows[i+1].cells[0]; c1 = tbl_pre.rows[i+1].cells[1]
    shade_cell(c0, bg); shade_cell(c1, bg)
    for c, t, bold in [(c0, req, True), (c1, det, False)]:
        p = c.paragraphs[0]
        p.paragraph_format.space_before = Pt(4)
        p.paragraph_format.space_after  = Pt(4)
        r = p.add_run(t); r.font.size = Pt(10); r.font.bold = bold

doc.add_paragraph()

# ═══════════════════════════════════════════════════════════════════════════════
#  STEP 1 — DNS
# ═══════════════════════════════════════════════════════════════════════════════
add_step(doc, 1, 'Point Your Domain to the VPS')
add_body(doc, 'You need to create a DNS A record so your domain resolves to the VPS IP address.')
add_num(doc, 'Log in to Hostinger → go to Domains → click on your domain.')
add_num(doc, 'Open the DNS / Nameservers tab.')
add_num(doc, 'Click Add Record and fill in:')
add_bullet(doc, 'Type:  A', 1)
add_bullet(doc, 'Name:  @  (for the root domain, e.g. menro.com)  OR  menro  (for a subdomain, e.g. menro.example.com)', 1)
add_bullet(doc, 'Points to:  YOUR_VPS_IP  (find this in Hostinger → VPS → your server)', 1)
add_bullet(doc, 'TTL:  3600  (default is fine)', 1)
add_num(doc, 'Click Save.')
add_num(doc, 'Wait 5–15 minutes for DNS to propagate. You can check at: https://dnschecker.org')
add_tip(doc, '💡 Tip:', 'Also add a second A record with Name: www  pointing to the same IP so that both www.yourdomain.com and yourdomain.com work.')

# ═══════════════════════════════════════════════════════════════════════════════
#  STEP 2 — SSH
# ═══════════════════════════════════════════════════════════════════════════════
add_step(doc, 2, 'SSH into the VPS')
add_body(doc, 'Open a terminal on your computer and connect to the VPS:')

add_code(doc, '# From Mac / Linux terminal:')
add_code(doc, 'ssh root@YOUR_VPS_IP')
doc.add_paragraph()
add_code(doc, '# From Windows — open Windows Terminal or PuTTY:')
add_code(doc, '# In PuTTY: Host Name = YOUR_VPS_IP, Port = 22, Connection type = SSH')
add_code(doc, '# Then click Open and log in as: root')
doc.add_paragraph()

add_body(doc, 'Replace YOUR_VPS_IP with the actual IP shown in Hostinger → VPS.')
add_tip(doc, '🔑 First Login:', 'Hostinger will send you the root password by email when the VPS is created. You can also set it in the Hostinger VPS dashboard under "Change Password".')

doc.add_page_break()

# ═══════════════════════════════════════════════════════════════════════════════
#  STEP 3 — Deploy Script
# ═══════════════════════════════════════════════════════════════════════════════
add_step(doc, 3, 'Run the Automated Deploy Script')
add_body(doc, 'The project includes deploy.sh — a single script that installs and configures everything on the server automatically.')
add_body(doc, 'What the script installs:', bold=True)
for item in [
    'Nginx (web server)',
    'PHP 8.3 + all required extensions (FPM, MySQL, GD, ZIP, mbstring, intl, bcmath, opcache)',
    'MySQL 8 (database server)',
    'Composer (PHP dependency manager)',
    'Node.js 20 + npm (for building frontend assets)',
]:
    add_bullet(doc, item)

doc.add_paragraph()
add_body(doc, 'What the script configures:', bold=True)
for item in [
    'Creates the MySQL database and user with a random secure password',
    'Creates the Nginx virtual host for your domain',
    'Writes the Laravel .env file with all database credentials',
    'Applies PHP.ini performance tweaks (upload limits, opcache)',
    'If app files already exist: runs composer install, builds frontend, runs migrations, seeds the database',
]:
    add_bullet(doc, item)

doc.add_paragraph()
add_body(doc, 'Option A — Copy and paste directly on the server (recommended):', bold=True)
add_body(doc, '① Upload deploy.sh from your computer to the server using SCP:')
add_code(doc, '# Run this on your LOCAL computer (not on the server):')
add_code(doc, 'scp C:\\Users\\YourName\\menro\\deploy.sh root@YOUR_VPS_IP:/root/')
doc.add_paragraph()
add_body(doc, '② SSH into the server and run the script:')
add_code(doc, 'ssh root@YOUR_VPS_IP')
add_code(doc, 'bash /root/deploy.sh your-domain.com menro menro_user')
doc.add_paragraph()

add_body(doc, 'Option B — Run directly from GitHub (if your repo is public):', bold=True)
add_code(doc, 'curl -fsSL https://raw.githubusercontent.com/YOUR_USER/menro/main/deploy.sh \\')
add_code(doc, '  | bash -s -- your-domain.com menro menro_user')
doc.add_paragraph()

add_body(doc, 'The script takes 5–10 minutes. When it finishes it prints a summary like this:')
add_code(doc, '╔══════════════════════════════════════════════════════╗')
add_code(doc, '║           MENRO Deployment Complete!                 ║')
add_code(doc, '╚══════════════════════════════════════════════════════╝')
add_code(doc, '')
add_code(doc, '  Site URL:      http://your-domain.com')
add_code(doc, '  App path:      /var/www/menro')
add_code(doc, '  DB Name:       menro')
add_code(doc, '  DB User:       menro_user')
add_code(doc, '  DB Password:   a1B2c3D4e5F6g7H8i9J0')
doc.add_paragraph()
add_tip(doc, '⚠ Important:', 'Copy and save the DB Password that appears at the end of the script — you will need it in Step 5.', bg='FFF3CD', label_color='856404')

doc.add_page_break()

# ═══════════════════════════════════════════════════════════════════════════════
#  STEP 4 — Upload Files
# ═══════════════════════════════════════════════════════════════════════════════
add_step(doc, 4, 'Upload Your Application Files')
add_body(doc, 'The application files must be placed in /var/www/menro on the server. Choose one method:')

add_body(doc, 'Method A — Git Clone (recommended if your project is on GitHub):', bold=True)
add_code(doc, '# On the server — install git if needed:')
add_code(doc, 'apt-get install -y git')
add_code(doc, '')
add_code(doc, '# Clone the repository directly into the app folder:')
add_code(doc, 'git clone https://github.com/YOUR_USER/menro.git /var/www/menro')
doc.add_paragraph()
add_tip(doc, '🔒 Private repo?', 'Use a Personal Access Token instead of a password:\ngit clone https://YOUR_TOKEN@github.com/YOUR_USER/menro.git /var/www/menro')

doc.add_paragraph()
add_body(doc, 'Method B — SFTP Upload using FileZilla (if you do not use Git):', bold=True)
add_num(doc, 'Download and install FileZilla from: https://filezilla-project.org')
add_num(doc, 'Open FileZilla and connect:')
add_bullet(doc, 'Host:      sftp://YOUR_VPS_IP', 1)
add_bullet(doc, 'Username:  root', 1)
add_bullet(doc, 'Password:  your root password', 1)
add_bullet(doc, 'Port:      22', 1)
add_num(doc, 'Click Quickconnect.')
add_num(doc, 'On the left panel, navigate to your MENRO project folder on your computer.')
add_num(doc, 'On the right panel, navigate to /var/www/menro/')
add_num(doc, 'Select ALL files and folders inside the project (Ctrl+A) and drag them to the right panel.')
add_num(doc, 'Wait for all files to finish uploading.')

add_tip(doc, '📌 Note:', 'Do NOT upload the vendor/ folder or node_modules/ folder — these are installed on the server in Step 6. Make sure .gitignore excludes them.', bg='E3F2FD', label_color='0D47A1')

doc.add_page_break()

# ═══════════════════════════════════════════════════════════════════════════════
#  STEP 5 — .env
# ═══════════════════════════════════════════════════════════════════════════════
add_step(doc, 5, 'Configure the Environment File (.env)')
add_body(doc, 'The .env file contains your app settings and database credentials. The deploy script already created this file, but confirm the values are correct:')

add_code(doc, '# On the server, open the file with nano:')
add_code(doc, 'nano /var/www/menro/.env')
doc.add_paragraph()

add_body(doc, 'Verify or update these values:')
add_code(doc, 'APP_NAME=MENRO')
add_code(doc, 'APP_ENV=production')
add_code(doc, 'APP_DEBUG=false')
add_code(doc, 'APP_KEY=base64:...       # already generated by deploy.sh')
add_code(doc, 'APP_URL=https://your-domain.com  # use https:// NOT http://')
add_code(doc, '')
add_code(doc, 'DB_CONNECTION=mysql')
add_code(doc, 'DB_HOST=127.0.0.1')
add_code(doc, 'DB_PORT=3306')
add_code(doc, 'DB_DATABASE=menro')
add_code(doc, 'DB_USERNAME=menro_user')
add_code(doc, 'DB_PASSWORD=YOUR_DB_PASSWORD_FROM_STEP_3')
add_code(doc, '')
add_code(doc, 'SESSION_DRIVER=file')
add_code(doc, 'CACHE_DRIVER=file')
add_code(doc, 'QUEUE_CONNECTION=sync')
doc.add_paragraph()

add_body(doc, 'Save and exit nano:')
add_code(doc, 'Press  Ctrl + X  →  then  Y  →  then  Enter')
doc.add_paragraph()
add_tip(doc, '🔑 APP_KEY:', 'If APP_KEY is empty, generate it with:  php artisan key:generate --force', bg='E8F5E9', label_color='1B5E20')

doc.add_page_break()

# ═══════════════════════════════════════════════════════════════════════════════
#  STEP 6 — Laravel Setup
# ═══════════════════════════════════════════════════════════════════════════════
add_step(doc, 6, 'Finish Laravel Setup')
add_body(doc, 'Run these commands on the server to install dependencies, build assets, and initialize the database. Copy and paste the entire block at once:')

add_code(doc, 'cd /var/www/menro')
add_code(doc, '')
add_code_comment(doc, '# Set correct ownership so www-data can write')
add_code(doc, 'chown -R www-data:www-data /var/www/menro')
add_code(doc, '')
add_code_comment(doc, '# Install PHP dependencies')
add_code(doc, 'sudo -u www-data composer install --no-dev --optimize-autoloader')
add_code(doc, '')
add_code_comment(doc, '# Install Node packages and build frontend assets')
add_code(doc, 'sudo -u www-data npm ci --ignore-scripts')
add_code(doc, 'sudo -u www-data npm run build')
add_code(doc, 'rm -rf node_modules')
add_code(doc, '')
add_code_comment(doc, '# Generate app key (skip if deploy.sh already set it)')
add_code(doc, 'sudo -u www-data php artisan key:generate --force')
add_code(doc, '')
add_code_comment(doc, '# Create storage symlink (public uploads)')
add_code(doc, 'sudo -u www-data php artisan storage:link --force')
add_code(doc, '')
add_code_comment(doc, '# Create required storage directories')
add_code(doc, 'mkdir -p storage/app/livewire-tmp storage/app/public/avatars')
add_code(doc, 'chmod -R 775 storage bootstrap/cache')
add_code(doc, 'chown -R www-data:www-data storage bootstrap/cache')
add_code(doc, '')
add_code_comment(doc, '# Run database migrations')
add_code(doc, 'sudo -u www-data php artisan migrate --force')
add_code(doc, '')
add_code_comment(doc, '# Seed default data (roles, admin user, barangays, etc.)')
add_code(doc, 'sudo -u www-data php artisan db:seed --force')
add_code(doc, '')
add_code_comment(doc, '# Cache config and views for production performance')
add_code(doc, 'sudo -u www-data php artisan config:cache')
add_code(doc, 'sudo -u www-data php artisan view:cache')
add_code(doc, '')
add_code_comment(doc, '# Restart PHP-FPM')
add_code(doc, 'systemctl restart php8.3-fpm')
add_code(doc, 'systemctl reload nginx')
doc.add_paragraph()
add_tip(doc, '✅ Expected:', 'Each artisan command should end with a line like "Migration table created" or "Seeding complete." If you see an error, jump to the Troubleshooting section.', bg='E8F5E9', label_color='1B5E20')

doc.add_page_break()

# ═══════════════════════════════════════════════════════════════════════════════
#  STEP 7 — HTTPS
# ═══════════════════════════════════════════════════════════════════════════════
add_step(doc, 7, 'Enable HTTPS — Free SSL via Let\'s Encrypt')
add_body(doc, 'Secure your site with a free SSL certificate from Let\'s Encrypt using certbot:')

add_code(doc, '# Install certbot')
add_code(doc, 'apt-get install -y certbot python3-certbot-nginx')
add_code(doc, '')
add_code(doc, '# Obtain and install the SSL certificate')
add_code(doc, '# Replace both occurrences of your-domain.com with your actual domain')
add_code(doc, 'certbot --nginx -d your-domain.com -d www.your-domain.com')
doc.add_paragraph()

add_body(doc, 'Certbot will ask you a few questions:')
add_num(doc, 'Enter an email address — used for renewal reminders.')
add_num(doc, 'Agree to the Terms of Service — type  A  and press Enter.')
add_num(doc, 'Share email with EFF (optional) — type  N.')
add_num(doc, 'Certbot automatically updates the Nginx config and enables HTTPS.')
doc.add_paragraph()
add_body(doc, 'Test automatic renewal:')
add_code(doc, 'certbot renew --dry-run')
doc.add_paragraph()
add_tip(doc, '🔄 Auto-renew:', 'Certbot automatically adds a cron job to renew the certificate before it expires. No manual action needed every 90 days.', bg='E8F5E9', label_color='1B5E20')

# ═══════════════════════════════════════════════════════════════════════════════
#  STEP 8 — Verify
# ═══════════════════════════════════════════════════════════════════════════════
add_step(doc, 8, 'Verify the Deployment')
add_body(doc, 'Open your browser and go to: https://your-domain.com')
add_body(doc, 'You should see the MENRO login screen. Log in using the default credentials:')

creds = [
    ('admin',     'admin123',  'System Administrator'),
    ('menro',     'admin123',  'MENRO Officer'),
    ('encoder',   'admin123',  'Data Encoder'),
    ('inspector', 'admin123',  'Field Inspector'),
]
tbl_cred = doc.add_table(rows=1 + len(creds), cols=3)
tbl_cred.alignment = WD_TABLE_ALIGNMENT.LEFT
tbl_cred.columns[0].width = Cm(4)
tbl_cred.columns[1].width = Cm(4)
tbl_cred.columns[2].width = Cm(7)
set_table_borders(tbl_cred, '4CAF50', '4')
for c, t in zip(tbl_cred.rows[0].cells, ['Username', 'Password', 'Role']):
    shade_cell(c, '2E7D32')
    p = c.paragraphs[0]
    p.paragraph_format.space_before = Pt(4)
    p.paragraph_format.space_after  = Pt(4)
    r = p.add_run(t); r.font.bold = True; r.font.size = Pt(10.5)
    r.font.color.rgb = RGBColor(0xFF, 0xFF, 0xFF)
for i, (u, pw, role) in enumerate(creds):
    bg = 'F1F8E9' if i % 2 == 0 else 'FFFFFF'
    cells = tbl_cred.rows[i+1].cells
    shade_cell(cells[0], bg); shade_cell(cells[1], bg); shade_cell(cells[2], bg)
    for c, t, mono in [(cells[0], u, True), (cells[1], pw, True), (cells[2], role, False)]:
        p = c.paragraphs[0]
        p.paragraph_format.space_before = Pt(4)
        p.paragraph_format.space_after  = Pt(4)
        rn = p.add_run(t); rn.font.size = Pt(10)
        if mono: rn.font.name = 'Courier New'

doc.add_paragraph()
add_tip(doc, '⚠ Security:', 'Change ALL default passwords immediately after your first login! Go to Settings → User Management → Edit each user.', bg='FFEBEE', label_color='B71C1C')

doc.add_page_break()

# ═══════════════════════════════════════════════════════════════════════════════
#  UPDATING / RE-DEPLOY
# ═══════════════════════════════════════════════════════════════════════════════
ph = doc.add_paragraph()
r = ph.add_run('Updating the App (Re-deploy)')
r.font.size  = Pt(16)
r.font.bold  = True
r.font.color.rgb = RGBColor(0x1B, 0x5E, 0x20)
ph.paragraph_format.space_after = Pt(6)

add_body(doc, 'When you push new code to the server, run these commands:')
add_code(doc, 'cd /var/www/menro')
add_code(doc, '')
add_code_comment(doc, '# Pull the latest code')
add_code(doc, 'git pull origin main')
add_code(doc, '')
add_code_comment(doc, '# Update PHP & JS dependencies')
add_code(doc, 'sudo -u www-data composer install --no-dev --optimize-autoloader')
add_code(doc, 'sudo -u www-data npm ci --ignore-scripts && npm run build && rm -rf node_modules')
add_code(doc, '')
add_code_comment(doc, '# Run any new migrations')
add_code(doc, 'sudo -u www-data php artisan migrate --force')
add_code(doc, '')
add_code_comment(doc, '# Clear and rebuild caches')
add_code(doc, 'sudo -u www-data php artisan config:cache')
add_code(doc, 'sudo -u www-data php artisan view:cache')
add_code(doc, '')
add_code_comment(doc, '# Restart services')
add_code(doc, 'systemctl reload php8.3-fpm')
add_code(doc, 'systemctl reload nginx')
doc.add_paragraph()

# ═══════════════════════════════════════════════════════════════════════════════
#  TROUBLESHOOTING
# ═══════════════════════════════════════════════════════════════════════════════
ph = doc.add_paragraph()
r = ph.add_run('Troubleshooting')
r.font.size  = Pt(16)
r.font.bold  = True
r.font.color.rgb = RGBColor(0x1B, 0x5E, 0x20)
ph.paragraph_format.space_after = Pt(6)

issues = [
    ('500 Internal Server Error\non first load',
     'Run:  php artisan config:cache\nCheck .env values are correct (no extra spaces around =)'),
    ('White / blank screen',
     'Temporarily set  APP_DEBUG=true  in .env, reload the page to see the error message, then set it back to false'),
    ('Permission denied errors',
     'Run:  chmod -R 775 storage bootstrap/cache\n      chown -R www-data:www-data storage bootstrap/cache'),
    ('Database connection refused',
     'Verify  DB_HOST=127.0.0.1  and MySQL is running:\n      systemctl status mysql\n      systemctl start mysql'),
    ('Uploads not working\n(avatars, reports)',
     'Run:  php artisan storage:link --force\nCheck that  storage/app/livewire-tmp  and  storage/app/public/avatars  exist'),
    ('Nginx 502 Bad Gateway',
     'PHP-FPM stopped. Run:  systemctl restart php8.3-fpm'),
    ('Certbot fails — domain\nnot resolving',
     'DNS has not propagated yet. Wait 15 minutes and try again. Check: https://dnschecker.org'),
    ('Page not found (404)\nfor all routes',
     'Check Nginx config:  nginx -t\nMake sure try_files directive is present in /etc/nginx/sites-available/menro'),
    ('npm run build fails',
     'Make sure Node 20 is installed:  node --version\nThen:  npm cache clean --force && npm ci && npm run build'),
    ('Composer install fails\n(memory error)',
     'Add  COMPOSER_MEMORY_LIMIT=-1  before the command:\n      COMPOSER_MEMORY_LIMIT=-1 composer install --no-dev'),
]
tbl_ts = doc.add_table(rows=1 + len(issues), cols=2)
tbl_ts.alignment = WD_TABLE_ALIGNMENT.LEFT
tbl_ts.columns[0].width = Cm(5.5)
tbl_ts.columns[1].width = Cm(10)
set_table_borders(tbl_ts, '4CAF50', '4')
for c, t in zip(tbl_ts.rows[0].cells, ['Problem', 'Solution']):
    shade_cell(c, '2E7D32')
    p = c.paragraphs[0]
    p.paragraph_format.space_before = Pt(4)
    p.paragraph_format.space_after  = Pt(4)
    r = p.add_run(t); r.font.bold = True; r.font.size = Pt(10.5)
    r.font.color.rgb = RGBColor(0xFF, 0xFF, 0xFF)
for i, (prob, sol) in enumerate(issues):
    bg = 'FFEBEE' if i % 2 == 0 else 'FFF8F8'
    c0 = tbl_ts.rows[i+1].cells[0]; c1 = tbl_ts.rows[i+1].cells[1]
    shade_cell(c0, bg); shade_cell(c1, bg)
    for c, t, mono in [(c0, prob, False), (c1, sol, False)]:
        p = c.paragraphs[0]
        p.paragraph_format.space_before = Pt(4)
        p.paragraph_format.space_after  = Pt(4)
        r = p.add_run(t); r.font.size = Pt(9.5)

doc.add_paragraph()

# ═══════════════════════════════════════════════════════════════════════════════
#  SERVER FILE STRUCTURE
# ═══════════════════════════════════════════════════════════════════════════════
ph = doc.add_paragraph()
r = ph.add_run('Server File Structure')
r.font.size  = Pt(14)
r.font.bold  = True
r.font.color.rgb = RGBColor(0x1B, 0x5E, 0x20)
ph.paragraph_format.space_before = Pt(10)
ph.paragraph_format.space_after  = Pt(4)

add_code(doc, '/var/www/menro/              ← Laravel project root (all app files go here)')
add_code(doc, '/var/www/menro/public/       ← Nginx document root (only this folder is web-accessible)')
add_code(doc, '/var/www/menro/storage/      ← Logs, sessions, uploaded files, compiled views')
add_code(doc, '/etc/nginx/sites-available/menro   ← Nginx virtual host config')
add_code(doc, '/etc/php/8.3/fpm/            ← PHP-FPM configuration')
add_code(doc, '/etc/mysql/                  ← MySQL configuration')
doc.add_paragraph()

# ── Footer note ───────────────────────────────────────────────────────────────
p_footer = doc.add_paragraph()
p_footer.paragraph_format.space_before = Pt(20)
p_footer.alignment = WD_ALIGN_PARAGRAPH.CENTER
r_f = p_footer.add_run('MENRO Waste Management Information System  ·  Deployment Guide  ·  v1.0  ·  2026')
r_f.font.size  = Pt(8)
r_f.font.color.rgb = RGBColor(0xAA, 0xAA, 0xAA)

# ── Save ──────────────────────────────────────────────────────────────────────
out = r'C:\Users\jhonr\Herd\menro\MENRO_Hostinger_Deployment_Guide.docx'
doc.save(out)
print(f'Saved: {out}')
