<?php
declare(strict_types=1);

const PASSING_MARK = 60.0;
const COURSE_KEYS = ['course1', 'course2', 'course3'];
const DEFAULT_COURSE_NAMES = ['Course 1', 'Course 2', 'Course 3'];

$errors = [];
$submitted = [
    'name'    => '',
    'program' => '',
];
foreach (COURSE_KEYS as $i => $key) {
    $submitted[$key . '_name']  = DEFAULT_COURSE_NAMES[$i];
    $submitted[$key . '_grade'] = '';
}
$result = null;

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function letter_grade_for(float $pct): array
{
    if ($pct >= 97) return ['A+', 4.0];
    if ($pct >= 93) return ['A',  4.0];
    if ($pct >= 90) return ['A-', 3.7];
    if ($pct >= 87) return ['B+', 3.3];
    if ($pct >= 83) return ['B',  3.0];
    if ($pct >= 80) return ['B-', 2.7];
    if ($pct >= 77) return ['C+', 2.3];
    if ($pct >= 73) return ['C',  2.0];
    if ($pct >= 70) return ['C-', 1.7];
    if ($pct >= 67) return ['D+', 1.3];
    if ($pct >= PASSING_MARK) return ['D', 1.0];
    return ['F', 0.0];
}

function standing_for(float $gpa, bool $passed): string
{
    if (!$passed) return 'Academic Probation';
    if ($gpa >= 3.5) return "Dean's List";
    if ($gpa >= 3.0) return 'Good Standing';
    return 'Satisfactory Standing';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted['name']    = trim((string)($_POST['name'] ?? ''));
    $submitted['program'] = trim((string)($_POST['program'] ?? ''));
    foreach (COURSE_KEYS as $i => $key) {
        $rawName = trim((string)($_POST[$key . '_name'] ?? ''));
        $submitted[$key . '_name']  = $rawName === '' ? DEFAULT_COURSE_NAMES[$i] : $rawName;
        $submitted[$key . '_grade'] = trim((string)($_POST[$key . '_grade'] ?? ''));
    }

    if ($submitted['name'] === '') {
        $errors['name'] = 'Enter the student\'s name.';
    } elseif (strlen($submitted['name']) > 80) {
        $errors['name'] = 'Name is too long (80 characters max).';
    }

    if (strlen($submitted['program']) > 80) {
        $errors['program'] = 'This is too long (80 characters max).';
    }

    $courses = [];
    foreach (COURSE_KEYS as $key) {
        $nameKey  = $key . '_name';
        $gradeKey = $key . '_grade';

        if (strlen($submitted[$nameKey]) > 60) {
            $errors[$nameKey] = 'Course name is too long (60 characters max).';
        }

        $raw = $submitted[$gradeKey];
        if ($raw === '' || !is_numeric($raw)) {
            $errors[$gradeKey] = 'Enter a numeric grade (0–100).';
        } elseif ((float)$raw < 0 || (float)$raw > 100) {
            $errors[$gradeKey] = 'Grade must be between 0 and 100.';
        } else {
            $courses[] = [
                'name'  => $submitted[$nameKey],
                'grade' => (float)$raw,
            ];
        }
    }

    if (empty($errors)) {
        foreach ($courses as &$course) {
            [$letter, $points] = letter_grade_for($course['grade']);
            $course['letter'] = $letter;
            $course['points'] = $points;
        }
        unset($course);

        $average = round(array_sum(array_column($courses, 'grade')) / count($courses), 2);
        $gpa     = round(array_sum(array_column($courses, 'points')) / count($courses), 2);
        $passed  = $average >= PASSING_MARK;
        [$overallLetter] = letter_grade_for($average);

        $result = [
            'name'    => $submitted['name'],
            'program' => $submitted['program'],
            'average' => $average,
            'letter'  => $overallLetter,
            'gpa'     => $gpa,
            'standing'=> standing_for($gpa, $passed),
            'passed'  => $passed,
            'courses' => $courses,
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>College Grade Calculator</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>
<main class="sheet">
  <div class="card">
    <header class="masthead">
      <h1>College Grade Calculator</h1>
      <p>Enter a student's grades in three courses to get their average, letter grade, GPA, and academic standing.</p>
    </header>

    <form id="grade-form" method="post" action="" novalidate>
      <div class="field">
        <label for="name">Student name</label>
        <input
          type="text" id="name" name="name" maxlength="80"
          value="<?= e($submitted['name']) ?>"
          aria-invalid="<?= isset($errors['name']) ? 'true' : 'false' ?>"
          placeholder="e.g. Maria Santos">
        <span class="error" data-error-for="name"><?= isset($errors['name']) ? e($errors['name']) : '' ?></span>
      </div>

      <div class="field">
        <label for="program">Program &amp; year level <span class="hint" style="display:inline">(optional)</span></label>
        <input
          type="text" id="program" name="program" maxlength="80"
          value="<?= e($submitted['program']) ?>"
          aria-invalid="<?= isset($errors['program']) ? 'true' : 'false' ?>"
          placeholder="e.g. BS Computer Science, 2nd Year">
        <span class="error" data-error-for="program"><?= isset($errors['program']) ? e($errors['program']) : '' ?></span>
      </div>

      <?php foreach (COURSE_KEYS as $i => $key): $n = $i + 1; ?>
        <div class="course-row">
          <div class="field course-name-field">
            <label for="<?= $key ?>_name">Course <?= $n ?></label>
            <input
              type="text" id="<?= $key ?>_name" name="<?= $key ?>_name" maxlength="60"
              value="<?= e($submitted[$key . '_name']) ?>"
              aria-invalid="<?= isset($errors[$key . '_name']) ? 'true' : 'false' ?>"
              placeholder="e.g. Calculus 101">
            <span class="error" data-error-for="<?= $key ?>_name"><?= isset($errors[$key . '_name']) ? e($errors[$key . '_name']) : '' ?></span>
          </div>
          <div class="field course-grade-field">
            <label for="<?= $key ?>_grade">Grade</label>
            <input
              class="grade-input"
              type="number" id="<?= $key ?>_grade" name="<?= $key ?>_grade"
              min="0" max="100" step="0.01" inputmode="decimal"
              value="<?= e($submitted[$key . '_grade']) ?>"
              aria-invalid="<?= isset($errors[$key . '_grade']) ? 'true' : 'false' ?>"
              placeholder="0–100">
            <span class="error" data-error-for="<?= $key ?>_grade"><?= isset($errors[$key . '_grade']) ? e($errors[$key . '_grade']) : '' ?></span>
          </div>
        </div>
      <?php endforeach; ?>

      <p class="live-average" id="live-average">Live average will appear here as you type.</p>

      <div class="actions">
        <button type="submit">Calculate result</button>
        <?php if ($result): ?>
          <button type="button" id="reset-btn" class="btn-ghost">Calculate another</button>
        <?php endif; ?>
      </div>
    </form>

    <?php if ($result): ?>
      <section class="result <?= $result['passed'] ? 'is-pass' : 'is-fail' ?>" aria-live="polite">
        <div class="result-top">
          <div class="student">
            <h2><?= e($result['name']) ?></h2>
            <?php if ($result['program'] !== ''): ?>
              <div class="section-line"><?= e($result['program']) ?></div>
            <?php endif; ?>
          </div>
          <div class="stamp"><?= $result['passed'] ? 'PASSED' : 'FAILED' ?></div>
        </div>

        <div class="stats-row">
          <div class="stat">
            <span class="stat-number"><?= number_format($result['average'], 2) ?>%</span>
            <span class="stat-label">average</span>
          </div>
          <div class="stat">
            <span class="stat-number"><?= e($result['letter']) ?></span>
            <span class="stat-label">letter grade</span>
          </div>
          <div class="stat">
            <span class="stat-number"><?= number_format($result['gpa'], 2) ?></span>
            <span class="stat-label">GPA / 4.00</span>
          </div>
        </div>

        <p class="remark"><?= e($result['standing']) ?></p>

        <table>
          <thead>
            <tr><th>Course</th><th class="num">Grade</th><th class="num">Letter</th></tr>
          </thead>
          <tbody>
            <?php foreach ($result['courses'] as $course): ?>
              <tr>
                <td><?= e($course['name']) ?></td>
                <td class="num"><?= number_format($course['grade'], 2) ?></td>
                <td class="num"><?= e($course['letter']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </section>
    <?php endif; ?>
  </div>

  <p class="footnote">Passing mark: <?= number_format(PASSING_MARK, 0) ?>% (D) · GPA on a 4.00 scale · Runs on PHP, no database required.</p>
</main>

<script src="script.js"></script>
</body>
</html>
