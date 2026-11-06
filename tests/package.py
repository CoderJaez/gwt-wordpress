"""Build an installable theme ZIP without development tools or test fixtures."""
from pathlib import Path
from zipfile import ZIP_DEFLATED, ZipFile

root = Path(__file__).resolve().parents[1]
output = root / '.test-artifacts' / 'gwt-dilg-29.2.0.zip'
output.parent.mkdir(exist_ok=True)
files = list(root.glob('*.php'))
files += [root / name for name in (
    'style.css', 'theme.css', 'theme.json', 'rtl.css', 'screenshot.png',
    'favicon.ico', 'README.md', 'CAROUSEL.md', 'MIGRATION.md', 'TESTING.md',
)]
for folder in ('inc', 'template-parts', 'css', 'js', 'images', 'languages'):
    files += [file for file in (root / folder).rglob('*') if file.is_file()]
with ZipFile(output, 'w', ZIP_DEFLATED) as archive:
    for file in sorted(files):
        archive.write(file, Path('gwt-wordpress') / file.relative_to(root))
with ZipFile(output) as archive:
    assert archive.testzip() is None
    assert 'gwt-wordpress/style.css' in archive.namelist()
    assert 'gwt-wordpress/index.php' in archive.namelist()
    assert not any('/tests/' in name or '/.test-tools/' in name or '/.git/' in name for name in archive.namelist())
print(f'{output} ({output.stat().st_size:,} bytes, {len(files)} files)')
