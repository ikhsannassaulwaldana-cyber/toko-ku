<?php
require 'config.php';
require_role(['admin']);

function report_date(string $value): ?string {
    $date = DateTime::createFromFormat('!Y-m-d', $value);
    $errors = DateTime::getLastErrors();
    if (!$date || ($errors !== false && ($errors['warning_count'] || $errors['error_count'])) || $date->format('Y-m-d') !== $value) {
        return null;
    }

    return $value;
}

$today = new DateTimeImmutable('today');
$defaultStart = $today->modify('-29 days')->format('Y-m-d');
$startInput = isset($_GET['start_date']) && is_string($_GET['start_date']) ? $_GET['start_date'] : $defaultStart;
$endInput = isset($_GET['end_date']) && is_string($_GET['end_date']) ? $_GET['end_date'] : $today->format('Y-m-d');
$period = isset($_GET['period']) && is_string($_GET['period']) ? $_GET['period'] : 'daily';
$allowedPeriods = ['daily', 'weekly', 'monthly', 'yearly'];
$errors = [];

$startDate = report_date((string)$startInput);
$endDate = report_date((string)$endInput);
if ($startDate === null || $endDate === null) {
    $errors[] = 'Tanggal mulai dan tanggal akhir harus valid.';
}
if (!in_array($period, $allowedPeriods, true)) {
    $period = 'daily';
}
if ($startDate !== null && $endDate !== null && $startDate > $endDate) {
    $errors[] = 'Tanggal mulai tidak boleh melewati tanggal akhir.';
}

$details = [];
$turnover = 0.0;
$transactionCount = 0;
$itemsSold = 0;
$chartRows = [];

if (!$errors) {
    $rangeSql = 'created_at >= ? AND created_at < DATE_ADD(?, INTERVAL 1 DAY)';
    $rangeParams = [$startDate, $endDate];

    $summary = $pdo->prepare("SELECT COALESCE(SUM(total), 0) AS turnover, COUNT(*) AS transaction_count FROM transactions WHERE $rangeSql");
    $summary->execute($rangeParams);
    $summaryRow = $summary->fetch();
    $turnover = (float)$summaryRow['turnover'];
    $transactionCount = (int)$summaryRow['transaction_count'];

    $quantity = $pdo->prepare("SELECT COALESCE(SUM(i.qty), 0) FROM transaction_items i JOIN transactions t ON t.id = i.transaction_id WHERE t.$rangeSql");
    $quantity->execute($rangeParams);
    $itemsSold = (int)$quantity->fetchColumn();

    $periodExpression = [
        'daily' => "DATE_FORMAT(created_at, '%Y-%m-%d')",
        'weekly' => "DATE_FORMAT(DATE_SUB(DATE(created_at), INTERVAL WEEKDAY(created_at) DAY), '%Y-%m-%d')",
        'monthly' => "DATE_FORMAT(created_at, '%Y-%m')",
        'yearly' => "DATE_FORMAT(created_at, '%Y')",
    ][$period];

    $chartQuery = $pdo->prepare(
        "SELECT $periodExpression AS period_key, COALESCE(SUM(total), 0) AS turnover, COUNT(*) AS transaction_count
         FROM transactions
         WHERE $rangeSql
         GROUP BY period_key
         ORDER BY period_key"
    );
    $chartQuery->execute($rangeParams);
    $chartRows = $chartQuery->fetchAll();

    $detailQuery = $pdo->prepare(
        'SELECT t.kode, u.nama AS kasir, i.nama_produk, i.qty, i.harga, i.subtotal, t.created_at
         FROM transactions t
         JOIN users u ON u.id = t.user_id
         JOIN transaction_items i ON i.transaction_id = t.id
         WHERE t.created_at >= ? AND t.created_at < DATE_ADD(?, INTERVAL 1 DAY)
         ORDER BY t.created_at DESC, t.id DESC, i.id ASC'
    );
    $detailQuery->execute($rangeParams);
    $details = $detailQuery->fetchAll();

    if (($_GET['export'] ?? '') === 'csv') {
        $fileName = 'laporan-penjualan-' . $startDate . '-sampai-' . $endDate . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, ['Kode Transaksi', 'Nama Kasir', 'Nama Barang', 'Jumlah', 'Harga', 'Subtotal', 'Tanggal'], ';');
        foreach ($details as $row) {
            $csvRow = [
                $row['kode'],
                $row['kasir'],
                $row['nama_produk'],
                $row['qty'],
                $row['harga'],
                $row['subtotal'],
                $row['created_at'],
            ];
            foreach ($csvRow as &$value) {
                if (is_string($value) && preg_match('/^[\s]*[=+\-@]/', $value)) {
                    $value = "'" . $value;
                }
            }
            unset($value);
            fputcsv($output, $csvRow, ';');
        }
        fclose($output);
        exit;
    }
}

$chartLabels = [];
$chartTurnover = [];
$chartTransactions = [];
$periodLabels = ['daily' => 'harian', 'weekly' => 'mingguan', 'monthly' => 'bulanan', 'yearly' => 'tahunan'];
foreach ($chartRows as $row) {
    $key = $row['period_key'];
    if ($period === 'weekly') {
        $label = 'Minggu ' . date('d M Y', strtotime($key));
    } elseif ($period === 'monthly') {
        $label = date('M Y', strtotime($key . '-01'));
    } elseif ($period === 'yearly') {
        $label = $key;
    } else {
        $label = date('d M Y', strtotime($key));
    }
    $chartLabels[] = $label;
    $chartTurnover[] = (float)$row['turnover'];
    $chartTransactions[] = (int)$row['transaction_count'];
}

require 'partials.php';
layout_start('Laporan Penjualan', 'reports');
?>
<section class="report-page">
    <div class="report-intro">
        <div>
            <span class="report-eyebrow">RINGKASAN BISNIS</span>
            <h2>Performa penjualan</h2>
            <p>Pantau omzet dan aktivitas transaksi dalam periode pilihan.</p>
        </div>
        <div class="report-actions no-print">
            <a class="btn btn-soft" href="?<?= e(http_build_query(['start_date' => $startInput, 'end_date' => $endInput, 'period' => $period, 'export' => 'csv'])) ?>">↓ Export Excel (CSV)</a>
            <button type="button" class="btn" onclick="window.print()">Cetak laporan</button>
        </div>
    </div>

    <?php if ($errors): ?>
        <div class="alert" role="alert"><?php foreach ($errors as $error): ?><div><?= e($error) ?></div><?php endforeach; ?></div>
    <?php endif; ?>

    <form class="panel report-filter no-print" method="get">
        <div class="field">
            <label for="start_date">Tanggal mulai</label>
            <input class="input" type="date" id="start_date" name="start_date" required value="<?= e($startInput) ?>">
        </div>
        <div class="field">
            <label for="end_date">Tanggal akhir</label>
            <input class="input" type="date" id="end_date" name="end_date" required value="<?= e($endInput) ?>">
        </div>
        <div class="field">
            <label for="period">Ringkasan grafik</label>
            <select class="select" id="period" name="period">
                <option value="daily" <?= $period === 'daily' ? 'selected' : '' ?>>Harian</option>
                <option value="weekly" <?= $period === 'weekly' ? 'selected' : '' ?>>Mingguan</option>
                <option value="monthly" <?= $period === 'monthly' ? 'selected' : '' ?>>Bulanan</option>
                <option value="yearly" <?= $period === 'yearly' ? 'selected' : '' ?>>Tahunan</option>
            </select>
        </div>
        <button class="btn report-filter-button" type="submit">Terapkan filter</button>
    </form>

    <?php if (!$errors): ?>
    <div class="grid report-stats">
        <article class="stat report-stat">
            <span class="stat-icon">↗</span>
            <div class="stat-label">Total omzet</div>
            <div class="stat-value report-money"><?= rupiah($turnover) ?></div>
            <div class="report-stat-note">Periode terpilih</div>
        </article>
        <article class="stat report-stat">
            <span class="stat-icon">▤</span>
            <div class="stat-label">Jumlah transaksi</div>
            <div class="stat-value"><?= number_format($transactionCount, 0, ',', '.') ?></div>
            <div class="report-stat-note">Transaksi tercatat</div>
        </article>
        <article class="stat report-stat">
            <span class="stat-icon">▣</span>
            <div class="stat-label">Barang terjual</div>
            <div class="stat-value"><?= number_format($itemsSold, 0, ',', '.') ?></div>
            <div class="report-stat-note">Total unit barang</div>
        </article>
    </div>

    <section class="panel report-chart-panel">
        <div class="panel-head">
            <div><h3>Grafik penjualan</h3><div class="sub">Omzet dan jumlah transaksi secara <?= e($periodLabels[$period]) ?>.</div></div>
            <span class="badge"><?= e(date('d M Y', strtotime($startDate))) ?> — <?= e(date('d M Y', strtotime($endDate))) ?></span>
        </div>
        <div class="report-chart-wrap"><canvas id="salesChart" aria-label="Grafik penjualan" role="img"></canvas></div>
    </section>

    <section class="panel report-detail-panel">
        <div class="panel-head">
            <div><h3>Rincian transaksi</h3><div class="sub"><?= count($details) ?> baris barang pada periode ini</div></div>
        </div>
        <div class="table-wrap">
            <table class="report-table">
                <thead><tr><th>Kode transaksi</th><th>Nama kasir</th><th>Nama barang</th><th>Jumlah</th><th>Harga</th><th>Subtotal</th><th>Tanggal</th></tr></thead>
                <tbody>
                <?php foreach ($details as $row): ?>
                    <tr>
                        <td><b><?= e($row['kode']) ?></b></td>
                        <td><?= e($row['kasir']) ?></td>
                        <td><?= e($row['nama_produk']) ?></td>
                        <td><?= (int)$row['qty'] ?></td>
                        <td><?= rupiah($row['harga']) ?></td>
                        <td><b><?= rupiah($row['subtotal']) ?></b></td>
                        <td><?= e(date('d/m/Y H:i', strtotime($row['created_at']))) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$details): ?><tr><td class="report-empty" colspan="7">Belum ada transaksi pada rentang tanggal ini.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
    <?php endif; ?>
</section>
<?php if (!$errors): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const reportLabels = <?= json_encode($chartLabels, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const reportTurnover = <?= json_encode($chartTurnover, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const reportTransactions = <?= json_encode($chartTransactions, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const chartCanvas = document.getElementById('salesChart');
if (chartCanvas && window.Chart) {
    new Chart(chartCanvas, {
        type: 'bar',
        data: {
            labels: reportLabels,
            datasets: [
                { label: 'Omzet (Rp)', data: reportTurnover, backgroundColor: 'rgba(98, 87, 232, .78)', borderRadius: 8, yAxisID: 'y' },
                { label: 'Transaksi', data: reportTransactions, type: 'line', borderColor: '#22a477', backgroundColor: '#22a477', tension: .35, yAxisID: 'y1' }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            animation: { duration: 650 },
            scales: {
                y: { beginAtZero: true, ticks: { callback: value => 'Rp ' + new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(value) } },
                y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, ticks: { precision: 0 } }
            }
        }
    });
}
</script>
<?php endif; ?>
<?php layout_end(); ?>
