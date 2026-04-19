<?php
require 'db.php';
require_login();

$page_title = 'Analitika — TaskFlow';
$uid = $_SESSION['user_id'];

// Ümumi statistika
$overview = $pdo->prepare("
    SELECT
        COUNT(*) as total,
        SUM(status='todo') as todo,
        SUM(status='inprogress') as inprogress,
        SUM(status='done') as done,
        SUM(priority='high') as high_p,
        SUM(priority='medium') as medium_p,
        SUM(priority='low') as low_p,
        SUM(deadline < CURDATE() AND status != 'done') as overdue
    FROM tasks WHERE user_id = ?
");
$overview->execute([$uid]);
$ov = $overview->fetch();
$done_pct = $ov['total'] > 0 ? round(($ov['done'] / $ov['total']) * 100) : 0;

// Son 7 gündə tamamlanan tapşırıqlar (gündəlik)
$daily = $pdo->prepare("
    SELECT DATE(created_at) as day, COUNT(*) as cnt
    FROM tasks
    WHERE user_id = ? AND status = 'done'
      AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY day ASC
");
$daily->execute([$uid]);
$daily_rows = $daily->fetchAll();

// Son 7 günü dolduruq (boş günlər üçün 0)
$daily_map = [];
for ($i = 6; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime("-$i days"));
    $daily_map[$day] = 0;
}
foreach ($daily_rows as $row) {
    $daily_map[$row['day']] = (int)$row['cnt'];
}

// Etiketa görə
$labels_q = $pdo->prepare("
    SELECT label, COUNT(*) as cnt
    FROM tasks WHERE user_id = ? AND label IS NOT NULL AND label != ''
    GROUP BY label ORDER BY cnt DESC LIMIT 8
");
$labels_q->execute([$uid]);
$label_rows = $labels_q->fetchAll();

// Bu ay vs keçən ay
$this_month = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = ? AND MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())");
$this_month->execute([$uid]);
$this_month_cnt = (int)$this_month->fetchColumn();

$last_month = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = ? AND MONTH(created_at)=MONTH(DATE_SUB(CURDATE(),INTERVAL 1 MONTH)) AND YEAR(created_at)=YEAR(DATE_SUB(CURDATE(),INTERVAL 1 MONTH))");
$last_month->execute([$uid]);
$last_month_cnt = (int)$last_month->fetchColumn();

include 'header.php';
?>

<main class="container">
    <div class="page-title">
        <h2>📊 Analitika</h2>
    </div>

    <!-- Ümumi statistika -->
    <div class="stats-row">
        <div class="stat-card stat-total">
            <span class="stat-num"><?= $ov['total'] ?></span>
            <span class="stat-label">Cəmi Tapşırıq</span>
        </div>
        <div class="stat-card stat-done">
            <span class="stat-num"><?= $done_pct ?>%</span>
            <span class="stat-label">Tamamlanma Faizi</span>
        </div>
        <div class="stat-card stat-urgent">
            <span class="stat-num"><?= $ov['overdue'] ?></span>
            <span class="stat-label">⚠️ Vaxtı Keçmiş</span>
        </div>
        <div class="stat-card stat-inprogress">
            <span class="stat-num"><?= $this_month_cnt ?></span>
            <span class="stat-label">Bu Ay Əlavə</span>
        </div>
    </div>

    <div class="analytics-grid">

        <!-- Tamamlanma faizi progress -->
        <div class="analytics-card">
            <h3>📈 Tamamlanma Vəziyyəti</h3>
            <div class="progress-section">
                <div class="progress-item">
                    <div class="progress-label"><span>✅ Tamamlandı</span><span><?= $ov['done'] ?></span></div>
                    <div class="progress-bar"><div class="progress-fill fill-done" style="width:<?= $ov['total']>0 ? round($ov['done']/$ov['total']*100) : 0 ?>%"></div></div>
                </div>
                <div class="progress-item">
                    <div class="progress-label"><span>🔄 Davam edir</span><span><?= $ov['inprogress'] ?></span></div>
                    <div class="progress-bar"><div class="progress-fill fill-inprogress" style="width:<?= $ov['total']>0 ? round($ov['inprogress']/$ov['total']*100) : 0 ?>%"></div></div>
                </div>
                <div class="progress-item">
                    <div class="progress-label"><span>⏳ Gözləyir</span><span><?= $ov['todo'] ?></span></div>
                    <div class="progress-bar"><div class="progress-fill fill-todo" style="width:<?= $ov['total']>0 ? round($ov['todo']/$ov['total']*100) : 0 ?>%"></div></div>
                </div>
            </div>
        </div>

        <!-- Prioritet bölgüsü -->
        <div class="analytics-card">
            <h3>🎯 Prioritet Bölgüsü</h3>
            <div class="priority-stats">
                <div class="pri-item pri-high">
                    <span class="pri-icon">🔴</span>
                    <div>
                        <strong>Yüksək</strong>
                        <span><?= $ov['high_p'] ?> tapşırıq</span>
                    </div>
                    <span class="pri-num"><?= $ov['total']>0 ? round($ov['high_p']/$ov['total']*100) : 0 ?>%</span>
                </div>
                <div class="pri-item pri-medium">
                    <span class="pri-icon">🟡</span>
                    <div>
                        <strong>Orta</strong>
                        <span><?= $ov['medium_p'] ?> tapşırıq</span>
                    </div>
                    <span class="pri-num"><?= $ov['total']>0 ? round($ov['medium_p']/$ov['total']*100) : 0 ?>%</span>
                </div>
                <div class="pri-item pri-low">
                    <span class="pri-icon">🟢</span>
                    <div>
                        <strong>Aşağı</strong>
                        <span><?= $ov['low_p'] ?> tapşırıq</span>
                    </div>
                    <span class="pri-num"><?= $ov['total']>0 ? round($ov['low_p']/$ov['total']*100) : 0 ?>%</span>
                </div>
            </div>
        </div>

        <!-- Son 7 gün chart (CSS chart) -->
        <div class="analytics-card analytics-wide">
            <h3>📅 Son 7 Gündə Tamamlanan Tapşırıqlar</h3>
            <?php $max_daily = max(array_values($daily_map) ?: [1]); ?>
            <div class="bar-chart">
                <?php foreach ($daily_map as $day => $cnt): ?>
                <div class="bar-col">
                    <span class="bar-val"><?= $cnt ?></span>
                    <div class="bar" style="height:<?= $max_daily > 0 ? round(($cnt/$max_daily)*120) : 0 ?>px"></div>
                    <span class="bar-label"><?= date('d/m', strtotime($day)) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Etiketlər -->
        <?php if (!empty($label_rows)): ?>
        <div class="analytics-card">
            <h3>🏷 Etiketlər üzrə</h3>
            <div class="label-stats">
                <?php $max_l = max(array_column($label_rows, 'cnt')); ?>
                <?php foreach ($label_rows as $lr): ?>
                <div class="label-stat-item">
                    <span class="label-tag"><?= htmlspecialchars($lr['label']) ?></span>
                    <div class="progress-bar" style="flex:1">
                        <div class="progress-fill fill-done" style="width:<?= round($lr['cnt']/$max_l*100) ?>%"></div>
                    </div>
                    <span><?= $lr['cnt'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Bu ay vs keçən ay -->
        <div class="analytics-card">
            <h3>📆 Aylıq Müqayisə</h3>
            <div class="month-compare">
                <div class="month-item">
                    <span class="month-label">Bu ay</span>
                    <span class="month-num"><?= $this_month_cnt ?></span>
                    <span class="month-sub">tapşırıq əlavə edildi</span>
                </div>
                <div class="month-divider">VS</div>
                <div class="month-item">
                    <span class="month-label">Keçən ay</span>
                    <span class="month-num"><?= $last_month_cnt ?></span>
                    <span class="month-sub">tapşırıq əlavə edildi</span>
                </div>
            </div>
            <?php if ($last_month_cnt > 0): 
                $diff = $this_month_cnt - $last_month_cnt;
                $pct  = round(abs($diff) / $last_month_cnt * 100);
            ?>
            <p class="month-trend <?= $diff >= 0 ? 'trend-up' : 'trend-down' ?>">
                <?= $diff >= 0 ? '📈 +' : '📉 ' ?><?= $diff ?> (<?= $pct ?>%) keçən aya nisbətən
            </p>
            <?php endif; ?>
        </div>

    </div>
</main>

</body>
</html>
