<?php
require_once 'db.php';
require_once 'auth.php';

// Check authentication
checkAuth();
$user = getCurrentUser();

// Get all subjects for filter dropdown for current user
$subjects_query = "SELECT DISTINCT subject FROM exams WHERE user_id = ? ORDER BY subject";
$stmt = $conn->prepare($subjects_query);
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$subjects_result = $stmt->get_result();

// Get all semesters for filter dropdown for current user
$semesters_query = "SELECT DISTINCT semester FROM exams WHERE user_id = ? ORDER BY semester";
$stmt = $conn->prepare($semesters_query);
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$semesters_result = $stmt->get_result();

// Handle filters
$subject_filter = isset($_GET['subject']) ? $_GET['subject'] : '';
$semester_filter = isset($_GET['semester']) ? $_GET['semester'] : '';
$sort_order = isset($_GET['sort']) ? $_GET['sort'] : 'exam_datetime';

// Build query with filters for current user only
$query = "SELECT * FROM exams WHERE user_id = ?";
$params = [$user['id']];
$types = "i";

if (!empty($subject_filter)) {
    $query .= " AND subject = ?";
    $params[] = $subject_filter;
    $types .= "s";
}

if (!empty($semester_filter)) {
    $query .= " AND semester = ?";
    $params[] = $semester_filter;
    $types .= "s";
}

$query .= " ORDER BY " . ($sort_order === 'urgency' ? 'exam_datetime ASC' : 'exam_datetime ASC');

$stmt = $conn->prepare($query);
if (count($params) > 1) {
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param("i", $user['id']);
}
$stmt->execute();
$result = $stmt->get_result();

// Get dashboard stats
$stats = getExamStats($user['id'], $conn);
$next_exam = getNextUpcomingExam($user['id'], $conn);

// Update priorities for all user's exams
$update_query = "SELECT id, exam_datetime FROM exams WHERE user_id = ?";
$stmt = $conn->prepare($update_query);
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$update_result = $stmt->get_result();

while ($exam = $update_result->fetch_assoc()) {
    $new_priority = calculateDynamicPriority($exam['exam_datetime']);
    $update_stmt = $conn->prepare("UPDATE exams SET priority = ? WHERE id = ?");
    $update_stmt->bind_param("si", $new_priority, $exam['id']);
    $update_stmt->execute();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyExamTrack - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <!-- Main Container -->
    <div class="main-container">
        <!-- Header -->
        <header class="header">
            <div class="header-brand">
                <div class="brand-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="brand-text">
                    <h1>MyExamTrack</h1>
                    <p>Modern exam management dashboard</p>
                </div>
            </div>            <div class="header-actions">
                <div class="user-info">
                    <span class="user-name"><?php echo htmlspecialchars($user['name']); ?></span>
                    <span class="user-roll"><?php echo htmlspecialchars($user['roll_number']); ?></span>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExamModal">
                    <i class="fas fa-plus"></i>
                    Add Exam
                </button>
                <a href="logout.php" class="btn btn-secondary">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </header>

        <!-- Dashboard Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon total">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Total Exams</div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon today">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo $stats['today']; ?></div>
                <div class="stat-label">Today</div>
            </div>            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon week">
                        <i class="fas fa-calendar-week"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo $stats['week']; ?></div>
                <div class="stat-label">This Week</div>
            </div>
            <div class="stat-card">
                <?php if ($next_exam): ?>
                    <?php $time_diff = getTimeDifference($next_exam['exam_datetime']); ?>
                    <div class="stat-header">
                        <div class="stat-icon next">
                            <i class="fas fa-rocket"></i>
                        </div>
                    </div>
                    <div class="stat-value" id="next-exam-countdown">
                        <?php if (is_array($time_diff)): ?>
                            <?php echo $time_diff['days']; ?>d <?php echo $time_diff['hours']; ?>h
                        <?php else: ?>
                            Passed
                        <?php endif; ?>
                    </div>
                    <div class="stat-label">Next: <?php echo htmlspecialchars($next_exam['subject']); ?></div>
                <?php else: ?>
                    <div class="stat-header">
                        <div class="stat-icon next">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="stat-value">All Clear!</div>
                    <div class="stat-label">No Exams</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Enhanced Filters -->
        <div class="filters-section">
            <div class="filters-header">
                <i class="fas fa-filter"></i>
                <h3>Filters & Sorting</h3>
            </div>
            <form method="GET">
                <div class="filters-grid">
                    <div class="form-group">
                        <label for="subject" class="form-label">
                            <i class="fas fa-book"></i>
                            Subject
                        </label>
                        <select name="subject" id="subject" class="form-select">
                            <option value="">All Subjects</option>
                            <?php 
                            $subjects_result->data_seek(0);
                            while ($subject = $subjects_result->fetch_assoc()): 
                            ?>
                                <option value="<?php echo htmlspecialchars($subject['subject']); ?>" 
                                        <?php echo $subject_filter === $subject['subject'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($subject['subject']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="semester" class="form-label">
                            <i class="fas fa-graduation-cap"></i>
                            Semester
                        </label>
                        <select name="semester" id="semester" class="form-select">
                            <option value="">All Semesters</option>                            <?php 
                            $semesters_result->data_seek(0);
                            while ($semester = $semesters_result->fetch_assoc()): 
                            ?>
                                <option value="<?php echo htmlspecialchars($semester['semester']); ?>" 
                                        <?php echo $semester_filter === $semester['semester'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($semester['semester']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="sort" class="form-label">
                            <i class="fas fa-sort"></i>
                            Sort by
                        </label>
                        <select name="sort" id="sort" class="form-select">
                            <option value="exam_datetime" <?php echo $sort_order === 'exam_datetime' ? 'selected' : ''; ?>>Date & Time</option>
                            <option value="urgency" <?php echo $sort_order === 'urgency' ? 'selected' : ''; ?>>Urgency Level</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="opacity: 0;">Actions</label>
                        <div style="display: flex; gap: 0.5rem;">
                            <button type="submit" class="btn btn-primary" style="flex: 1;">
                                <i class="fas fa-filter"></i>
                                Apply
                            </button>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Exam Cards -->
        <div class="exams-grid" id="examCards">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($exam = $result->fetch_assoc()): ?>
                    <?php 
                        $urgency = getUrgencyLevel($exam['exam_datetime']);
                        $time_diff = getTimeDifference($exam['exam_datetime']);
                        $priority_icon = getPriorityIcon($exam['priority']);
                    ?>
                    <div class="exam-card priority-<?php echo strtolower($exam['priority']); ?> urgency-<?php echo $urgency; ?> <?php echo ($next_exam && $next_exam['id'] == $exam['id']) ? 'next-exam-glow' : ''; ?>" 
                         data-exam-id="<?php echo $exam['id']; ?>"
                         data-exam-datetime="<?php echo $exam['exam_datetime']; ?>">                        <div class="exam-header">
                            <div>
                                <div class="exam-subject"><?php echo htmlspecialchars($exam['subject']); ?></div>
                                <div class="exam-semester">
                                    <i class="fas fa-graduation-cap"></i>
                                    <?php echo htmlspecialchars($exam['semester']); ?>
                                </div>
                            </div>
                            <div class="priority-badge">
                                <span class="priority-icon"><?php echo $priority_icon; ?></span>
                                <span class="priority-text"><?php echo $exam['priority']; ?></span>
                            </div>
                        </div>
                        
                        <div class="exam-details">
                            <div class="exam-detail">
                                <i class="fas fa-calendar"></i>
                                <span><?php echo date('M j, Y', strtotime($exam['exam_datetime'])); ?></span>
                            </div>
                            <div class="exam-detail">
                                <i class="fas fa-clock"></i>
                                <span><?php echo date('g:i A', strtotime($exam['exam_datetime'])); ?></span>
                            </div>
                            <div class="exam-detail">
                                <i class="fas fa-bookmark"></i>
                                <span><?php echo htmlspecialchars($exam['exam_type']); ?></span>
                            </div>
                        </div>
                        
                        <div class="countdown-section" id="countdown-<?php echo $exam['id']; ?>">
                            <?php if (is_array($time_diff)): ?>
                                <div class="countdown-timer">
                                    <div class="countdown-unit">
                                        <span class="countdown-number"><?php echo $time_diff['days']; ?></span>
                                        <span class="countdown-label">Days</span>
                                    </div>
                                    <div class="countdown-unit">
                                        <span class="countdown-number"><?php echo $time_diff['hours']; ?></span>
                                        <span class="countdown-label">Hours</span>
                                    </div>
                                    <div class="countdown-unit">
                                        <span class="countdown-number"><?php echo $time_diff['minutes']; ?></span>
                                        <span class="countdown-label">Mins</span>
                                    </div>
                                    <div class="countdown-unit">
                                        <span class="countdown-number"><?php echo $time_diff['seconds']; ?></span>
                                        <span class="countdown-label">Secs</span>
                                    </div>
                                </div>
                                <div class="exam-status">Time remaining until exam</div>
                            <?php else: ?>
                                <div class="exam-status" style="text-align: center; padding: 1rem; color: var(--text-muted);">
                                    <i class="fas fa-check-circle" style="margin-right: 0.5rem;"></i>
                                    <?php echo $time_diff; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="exam-actions">
                            <button class="btn btn-secondary" onclick="editExam(<?php echo $exam['id']; ?>)">
                                <i class="fas fa-edit"></i>
                                Edit
                            </button>
                            <button class="btn btn-danger" onclick="deleteExam(<?php echo $exam['id']; ?>)">
                                <i class="fas fa-trash"></i>
                                Delete
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <h3>Ready to Track Your Exams?</h3>
                        <p class="text-muted mb-4">
                            Start your exam preparation journey by adding your first exam. 
                            Stay organized, stay ahead! 🚀
                        </p>
                        <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#addExamModal">
                            <i class="fas fa-plus me-2"></i>Add Your First Exam
                        </button>
                        <div class="motivational-text mt-3">
                            <small class="text-muted">
                                <i class="fas fa-lightbulb me-1"></i>
                                Success is where preparation and opportunity meet!
                            </small>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Exam Modal -->
    <div class="modal fade" id="addExamModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Exam</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="add_exam.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" name="subject" required>
                        </div>                        <div class="mb-3">
                            <label for="exam_datetime" class="form-label">Exam Date & Time</label>
                            <input type="datetime-local" class="form-control" name="exam_datetime" required>
                        </div>
                        <div class="mb-3">
                            <label for="semester" class="form-label">Semester</label>
                            <input type="text" class="form-control" name="semester" placeholder="e.g., Semester 1, Fall 2025" required>
                        </div>
                        <div class="mb-3">
                            <label for="exam_type" class="form-label">Exam Type</label>
                            <select name="exam_type" class="form-select" required>
                                <option value="Core Subjects" selected>Core Subjects</option>
                                <option value="Electives">Electives</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="priority" class="form-label">Priority</label>
                            <select name="priority" class="form-select" required>
                                <option value="Low">Low</option>
                                <option value="Medium" selected>Medium</option>
                                <option value="High">High</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Exam</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Exam Modal -->
    <div class="modal fade" id="editExamModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Exam</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="edit_exam.php" method="POST">
                    <input type="hidden" name="id" id="edit_exam_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" name="subject" id="edit_subject" required>
                        </div>                        <div class="mb-3">
                            <label for="edit_exam_datetime" class="form-label">Exam Date & Time</label>
                            <input type="datetime-local" class="form-control" name="exam_datetime" id="edit_exam_datetime" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_semester" class="form-label">Semester</label>
                            <input type="text" class="form-control" name="semester" id="edit_semester" placeholder="e.g., Semester 1, Fall 2025" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_exam_type" class="form-label">Exam Type</label>
                            <select name="exam_type" class="form-select" id="edit_exam_type" required>
                                <option value="Core Subjects">Core Subjects</option>
                                <option value="Electives">Electives</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_priority" class="form-label">Priority</label>
                            <select name="priority" class="form-select" id="edit_priority" required>
                                <option value="Low">Low</option>
                                <option value="Medium">Medium</option>
                                <option value="High">High</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Exam</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Alert Modal -->
    <div class="modal fade" id="alertModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>Exam Alert
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="alertModalBody">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning" data-bs-dismiss="modal">Got it!</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>
