# College Grade Calculator

A small PHP web app: enter a student's name and grades for three courses,
and it calculates the average, letter grade, GPA (4.0 scale), and academic
standing (Dean's List / Good Standing / Satisfactory Standing / Academic
Probation).

## Files

- `index.php` — the form and all server-side logic (validation, average, letter grade, GPA, standing). This is the only PHP file; it processes its own POST submissions and re-renders itself with the result.
- `style.scss` — Sass source for the styling.
- `style.css` — compiled CSS (already built — you don't need Sass installed to run the app).
- `script.js` — front-end interactivity: a live average preview as you type, inline validation hints, and the result reveal animation. The server always re-validates, so this is a convenience layer only.

## Running it

You need PHP installed (PHP 7.4+ is fine; tested on 8.3). No database, no extra extensions. Keep all four files (`index.php`, `style.css`, `script.js`, plus `style.scss` if you want to edit styles) in the **same folder** — `index.php` links to `style.css` and `script.js` with no subfolder.

**Option A — PHP's built-in server**
```bash
cd grade-calculator
php -S localhost:8000
```
Then open http://localhost:8000 in a browser.

**Option B — XAMPP / WAMP / MAMP**
Copy the `grade-calculator` folder into your `htdocs` (or `www`) directory and visit
`http://localhost/grade-calculator/`.

## Customizing

- **Passing mark**: change `PASSING_MARK` near the top of `index.php` (defaults to 60%, a D — the lowest passing grade on the scale below).
- **Letter grade / GPA scale**: edit the `letter_grade_for()` function in `index.php`.
- **Academic standing thresholds**: edit `standing_for()` in `index.php`.
- **Number of courses**: the form supports 3 by default (`COURSE_KEYS`, `DEFAULT_COURSE_NAMES`). Add `course4`, etc. to both, and a matching `course4_name` / `course4_grade` block in the form, to support more.
- **Styling**: edit `style.scss`, then recompile with `sass style.scss style.css` (requires `npm install sass` or the Dart Sass CLI).

