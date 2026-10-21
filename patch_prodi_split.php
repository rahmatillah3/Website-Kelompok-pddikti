<?php
// Patch the prodi list view to show Active vs Inactive tables
$file = 'index.php';
$content = file_get_contents($file);

// Find the prodi grid section and the empty state section
// We'll replace from "<!-- Grid Kartu Program Studi -->" through "<!-- Empty State -->" closing div

// Pattern for the grid section start
$gridStart = '                <!-- Grid Kartu Program Studi -->' . "\r\n" .
             '                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="prodiGrid">' . "\r\n" .
             '                    <?php foreach ($prodi as $index => $p): ?>' . "\r\n" .
             '                    <?php' . "\r\n" .
             '                        $nama = $p["nama_prodi"] ?? "-";' . "\r\n" .
             '                        $jenjang = $p["jenjang_prodi"] ?? "-";' . "\r\n" .
             '                        $akreditasi = $p["akreditasi"] ?? "";' . "\r\n" .
             '                        $mahasiswa = $p["jumlah_mahasiswa"] ?? 0;' . "\r\n" .
             '                        $dosen = $p["jumlah_dosen"] ?? 0;' . "\r\n" .
             '                        $kode = $p["kode_prodi"] ?? "";' . "\r\n" .
             '                    ?>';

// Check if it's present
if (strpos($content, $gridStart) === false) {
    echo "FAILED: Could not find grid start marker\n";
    exit(1);
}

// Find the full block from grid start to end of empty state
$gridBlockPattern = '                <!-- Grid Kartu Program Studi -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="prodiGrid">
                    <?php foreach ($prodi as $index => $p): ?>
                    <?php
                        $nama = $p["nama_prodi"] ?? "-";
                        $jenjang = $p["jenjang_prodi"] ?? "-";
                        $akreditasi = $p["akreditasi"] ?? "";
                        $mahasiswa = $p["jumlah_mahasiswa"] ?? 0;
                        $dosen = $p["jumlah_dosen"] ?? 0;
                        $kode = $p["kode_prodi"] ?? "";
                    ?>';

// Helper function to generate a prodi card block
function renderProdiCardBlock($arrayVar, $sectionLabel, $badgeClass, $emptyMsg) {
    return '                <!-- ' . $sectionLabel . ' -->' . "\r\n" .
           '                <div class="space-y-4">' . "\r\n" .
           '                    <div class="flex items-center gap-3">' . "\r\n" .
           '                        <span class="' . $badgeClass . ' text-xs font-bold px-3 py-1 rounded-full">' . "\r\n" .
           '                            ' . $sectionLabel . "\r\n" .
           '                        </span>' . "\r\n" .
           '                        <span class="text-xs text-slate-500 dark:text-slate-400"><?= count($' . $arrayVar . ') ?> prodi</span>' . "\r\n" .
           '                    </div>' . "\r\n" .
           '                    <?php if (empty($' . $arrayVar . ')): ?>' . "\r\n" .
           '                    <p class="text-sm text-slate-400 italic py-4">' . $emptyMsg . '</p>' . "\r\n" .
           '                    <?php else: ?>' . "\r\n" .
           '                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 prodi-section" id="' . str_replace(' ', '', $arrayVar) . 'Grid">' . "\r\n" .
           '                        <?php foreach ($' . $arrayVar . ' as $index => $p): ?>' . "\r\n" .
           '                        <?php' . "\r\n" .
           '                            $nama = $p["nama_prodi"] ?? "-";' . "\r\n" .
           '                            $jenjang = $p["jenjang_prodi"] ?? "-";' . "\r\n" .
           '                            $akreditasi = $p["akreditasi"] ?? "";' . "\r\n" .
           '                            $mahasiswa = $p["jumlah_mahasiswa"] ?? 0;' . "\r\n" .
           '                            $dosen = $p["jumlah_dosen"] ?? 0;' . "\r\n" .
           '                            $kode = $p["kode_prodi"] ?? "";' . "\r\n" .
           '                            $statusProdi = $p["status_prodi"] ?? "";' . "\r\n" .
           '                        ?>';
}

// Rather than doing complex string manipulation, let's do a targeted line-range replacement
// Find positions of key markers
$markerStart = '                <!-- Grid Kartu Program Studi -->';
$markerEnd = '                </div>' . "\r\n" . "\r\n" .
             '                <!-- Empty State (Jika Pencarian Tidak Ditemukan) -->';

$posStart = strpos($content, $markerStart);
$posEnd   = strpos($content, '                <!-- Empty State (Jika Pencarian Tidak Ditemukan) -->');

if ($posStart === false || $posEnd === false) {
    echo "FAILED: Markers not found. posStart=" . ($posStart === false ? 'false' : $posStart) . " posEnd=" . ($posEnd === false ? 'false' : $posEnd) . "\n";
    exit(1);
}

// Extract the card template from the original (single card)
$origGrid = substr($content, $posStart, $posEnd - $posStart);

// Build the new section - two separate grids with section headers
$newSection = '                <!-- Grid Kartu Program Studi Aktif -->' . "\r\n" .
              '                <div class="space-y-6">' . "\r\n" .
              '                    <div class="flex items-center gap-3">' . "\r\n" .
              '                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">&#9989; Program Studi Aktif</h2>' . "\r\n" .
              '                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">' . "\r\n" .
              '                            <?= $total_prodi_aktif ?> Prodi' . "\r\n" .
              '                        </span>' . "\r\n" .
              '                    </div>' . "\r\n" .
              '                    <?php if (empty($prodi_aktif)): ?>' . "\r\n" .
              '                    <p class="text-sm text-slate-400 italic">Tidak ada program studi aktif.</p>' . "\r\n" .
              '                    <?php else: ?>' . "\r\n" .
              '                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 prodi-section" id="prodiGridAktif">' . "\r\n" .
              '                        <?php foreach ($prodi_aktif as $index => $p): ?>' . "\r\n" .
              '                        <?php' . "\r\n" .
              '                            $nama = $p["nama_prodi"] ?? "-";' . "\r\n" .
              '                            $jenjang = $p["jenjang_prodi"] ?? "-";' . "\r\n" .
              '                            $akreditasi = $p["akreditasi"] ?? "";' . "\r\n" .
              '                            $mahasiswa = $p["jumlah_mahasiswa"] ?? 0;' . "\r\n" .
              '                            $dosen = $p["jumlah_dosen"] ?? 0;' . "\r\n" .
              '                            $kode = $p["kode_prodi"] ?? "";' . "\r\n" .
              '                        ?>' . "\r\n" .
              '                        <div' . "\r\n" .
              '                            class="prodi-card group bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-xl hover:border-brand-300 dark:hover:border-brand-700 hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between"' . "\r\n" .
              '                            data-nama="<?= htmlspecialchars(strtolower($nama)) ?>"' . "\r\n" .
              '                            data-kode="<?= htmlspecialchars(strtolower($kode)) ?>"' . "\r\n" .
              '                            data-jenjang="<?= htmlspecialchars($jenjang) ?>"' . "\r\n" .
              '                            data-akreditasi="<?= htmlspecialchars($akreditasi) ?>"' . "\r\n" .
              '                        >' . "\r\n" .
              '                            <div>' . "\r\n" .
              '                                <!-- Header Kartu -->' . "\r\n" .
              '                                <div class="flex items-center justify-between gap-2 mb-3">' . "\r\n" .
              '                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold tracking-wider text-emerald-600 dark:text-emerald-400 uppercase">' . "\r\n" .
              '                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> AKTIF' . "\r\n" .
              '                                    </span>' . "\r\n" .
              '                                    <?php if (!empty($kode)): ?>' . "\r\n" .
              '                                    <span class="font-mono text-xs px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">' . "\r\n" .
              '                                        <?= htmlspecialchars($kode) ?>' . "\r\n" .
              '                                    </span>' . "\r\n" .
              '                                    <?php endif; ?>' . "\r\n" .
              '                                </div>' . "\r\n" .
              '                                <!-- Judul Program Studi -->' . "\r\n" .
              '                                <h3 class="text-lg font-bold text-slate-900 dark:text-white group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors line-clamp-2 min-h-[3.5rem]">' . "\r\n" .
              '                                    <?= htmlspecialchars($nama) ?>' . "\r\n" .
              '                                </h3>' . "\r\n" .
              '                                <!-- Badges (Jenjang & Akreditasi) -->' . "\r\n" .
              '                                <div class="flex flex-wrap items-center gap-2 mt-3">' . "\r\n" .
              '                                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold border <?= getJenjangBadgeClass($jenjang) ?>">' . "\r\n" .
              '                                        <?= htmlspecialchars($jenjang) ?>' . "\r\n" .
              '                                    </span>' . "\r\n" .
              '                                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold border <?= getAkreditasiBadgeClass($akreditasi) ?>">' . "\r\n" .
              '                                        <?= htmlspecialchars(badgeAkreditasi($akreditasi)) ?>' . "\r\n" .
              '                                    </span>' . "\r\n" .
              '                                </div>' . "\r\n" .
              '                            </div>' . "\r\n" .
              '                            <!-- Data & Tombol Aksi -->' . "\r\n" .
              '                            <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800/80 space-y-4">' . "\r\n" .
              '                                <div class="grid grid-cols-2 gap-2 text-xs">' . "\r\n" .
              '                                    <div class="bg-slate-50 dark:bg-slate-800/60 p-2.5 rounded-xl">' . "\r\n" .
              '                                        <span class="text-slate-500 dark:text-slate-400 block mb-0.5">Mahasiswa</span>' . "\r\n" .
              '                                        <strong class="text-sm text-slate-900 dark:text-white">' . "\r\n" .
              '                                            <?= is_numeric($mahasiswa) ? number_format((int)$mahasiswa, 0, \',\', \'.\') : \'-\' ?>' . "\r\n" .
              '                                        </strong>' . "\r\n" .
              '                                    </div>' . "\r\n" .
              '                                    <div class="bg-slate-50 dark:bg-slate-800/60 p-2.5 rounded-xl">' . "\r\n" .
              '                                        <span class="text-slate-500 dark:text-slate-400 block mb-0.5">Dosen</span>' . "\r\n" .
              '                                        <strong class="text-sm text-slate-900 dark:text-white">' . "\r\n" .
              '                                            <?= is_numeric($dosen) ? number_format((int)$dosen, 0, \',\', \'.\') : \'-\' ?>' . "\r\n" .
              '                                        </strong>' . "\r\n" .
              '                                    </div>' . "\r\n" .
              '                                </div>' . "\r\n" .
              '                                <a' . "\r\n" .
              '                                    href="index.php?prodi=<?= $index ?>"' . "\r\n" .
              '                                    class="w-full inline-flex items-center justify-center gap-1.5 py-2.5 px-4 rounded-xl text-sm font-semibold text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-950/60 hover:bg-brand-600 hover:text-white dark:hover:bg-brand-600 dark:hover:text-white border border-brand-200 dark:border-brand-800/60 transition-all"' . "\r\n" .
              '                                >' . "\r\n" .
              '                                    <span>Lihat Rincian</span>' . "\r\n" .
              '                                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">' . "\r\n" .
              '                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>' . "\r\n" .
              '                                    </svg>' . "\r\n" .
              '                                </a>' . "\r\n" .
              '                            </div>' . "\r\n" .
              '                        </div>' . "\r\n" .
              '                        <?php endforeach; ?>' . "\r\n" .
              '                    </div>' . "\r\n" .
              '                    <?php endif; ?>' . "\r\n" .
              '                </div>' . "\r\n\r\n" .
              '                <!-- Grid Kartu Program Studi Tidak Aktif -->' . "\r\n" .
              '                <div class="space-y-6">' . "\r\n" .
              '                    <div class="flex items-center gap-3">' . "\r\n" .
              '                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">&#128683; Program Studi Tidak Aktif</h2>' . "\r\n" .
              '                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/70 dark:text-rose-300 border border-rose-200 dark:border-rose-800">' . "\r\n" .
              '                            <?= $total_prodi_nonaktif ?> Prodi' . "\r\n" .
              '                        </span>' . "\r\n" .
              '                    </div>' . "\r\n" .
              '                    <?php if (empty($prodi_nonaktif)): ?>' . "\r\n" .
              '                    <p class="text-sm text-slate-400 italic">Tidak ada program studi tidak aktif.</p>' . "\r\n" .
              '                    <?php else: ?>' . "\r\n" .
              '                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 prodi-section" id="prodiGridNonaktif">' . "\r\n" .
              '                        <?php foreach ($prodi_nonaktif as $index => $p): ?>' . "\r\n" .
              '                        <?php' . "\r\n" .
              '                            $nama = $p["nama_prodi"] ?? "-";' . "\r\n" .
              '                            $jenjang = $p["jenjang_prodi"] ?? "-";' . "\r\n" .
              '                            $akreditasi = $p["akreditasi"] ?? "";' . "\r\n" .
              '                            $mahasiswa = $p["jumlah_mahasiswa"] ?? 0;' . "\r\n" .
              '                            $dosen = $p["jumlah_dosen"] ?? 0;' . "\r\n" .
              '                            $kode = $p["kode_prodi"] ?? "";' . "\r\n" .
              '                            $statusProdi = $p["status_prodi"] ?? "Tidak Aktif";' . "\r\n" .
              '                        ?>' . "\r\n" .
              '                        <div' . "\r\n" .
              '                            class="prodi-card group bg-white dark:bg-slate-900 rounded-3xl p-6 border border-rose-200/60 dark:border-rose-900/40 shadow-sm hover:shadow-lg hover:border-rose-300 dark:hover:border-rose-700 transition-all duration-300 flex flex-col justify-between opacity-80 hover:opacity-100"' . "\r\n" .
              '                            data-nama="<?= htmlspecialchars(strtolower($nama)) ?>"' . "\r\n" .
              '                            data-kode="<?= htmlspecialchars(strtolower($kode)) ?>"' . "\r\n" .
              '                            data-jenjang="<?= htmlspecialchars($jenjang) ?>"' . "\r\n" .
              '                            data-akreditasi="<?= htmlspecialchars($akreditasi) ?>"' . "\r\n" .
              '                        >' . "\r\n" .
              '                            <div>' . "\r\n" .
              '                                <!-- Header Kartu -->' . "\r\n" .
              '                                <div class="flex items-center justify-between gap-2 mb-3">' . "\r\n" .
              '                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold tracking-wider text-rose-600 dark:text-rose-400 uppercase">' . "\r\n" .
              '                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>' . "\r\n" .
              '                                        <?= htmlspecialchars($statusProdi) ?>' . "\r\n" .
              '                                    </span>' . "\r\n" .
              '                                    <?php if (!empty($kode)): ?>' . "\r\n" .
              '                                    <span class="font-mono text-xs px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">' . "\r\n" .
              '                                        <?= htmlspecialchars($kode) ?>' . "\r\n" .
              '                                    </span>' . "\r\n" .
              '                                    <?php endif; ?>' . "\r\n" .
              '                                </div>' . "\r\n" .
              '                                <!-- Judul Program Studi -->' . "\r\n" .
              '                                <h3 class="text-lg font-bold text-slate-500 dark:text-slate-400 line-clamp-2 min-h-[3.5rem]">' . "\r\n" .
              '                                    <?= htmlspecialchars($nama) ?>' . "\r\n" .
              '                                </h3>' . "\r\n" .
              '                                <!-- Badges (Jenjang & Status) -->' . "\r\n" .
              '                                <div class="flex flex-wrap items-center gap-2 mt-3">' . "\r\n" .
              '                                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold border <?= getJenjangBadgeClass($jenjang) ?>">' . "\r\n" .
              '                                        <?= htmlspecialchars($jenjang) ?>' . "\r\n" .
              '                                    </span>' . "\r\n" .
              '                                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300 border border-rose-200 dark:border-rose-800">' . "\r\n" .
              '                                        <?= htmlspecialchars($statusProdi) ?>' . "\r\n" .
              '                                    </span>' . "\r\n" .
              '                                </div>' . "\r\n" .
              '                            </div>' . "\r\n" .
              '                            <!-- Data & Tombol Aksi -->' . "\r\n" .
              '                            <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800/80 space-y-4">' . "\r\n" .
              '                                <div class="grid grid-cols-2 gap-2 text-xs">' . "\r\n" .
              '                                    <div class="bg-slate-50 dark:bg-slate-800/60 p-2.5 rounded-xl">' . "\r\n" .
              '                                        <span class="text-slate-500 dark:text-slate-400 block mb-0.5">Mahasiswa</span>' . "\r\n" .
              '                                        <strong class="text-sm text-slate-900 dark:text-white">' . "\r\n" .
              '                                            <?= is_numeric($mahasiswa) ? number_format((int)$mahasiswa, 0, \',\', \'.\') : \'-\' ?>' . "\r\n" .
              '                                        </strong>' . "\r\n" .
              '                                    </div>' . "\r\n" .
              '                                    <div class="bg-slate-50 dark:bg-slate-800/60 p-2.5 rounded-xl">' . "\r\n" .
              '                                        <span class="text-slate-500 dark:text-slate-400 block mb-0.5">Dosen</span>' . "\r\n" .
              '                                        <strong class="text-sm text-slate-900 dark:text-white">' . "\r\n" .
              '                                            <?= is_numeric($dosen) ? number_format((int)$dosen, 0, \',\', \'.\') : \'-\' ?>' . "\r\n" .
              '                                        </strong>' . "\r\n" .
              '                                    </div>' . "\r\n" .
              '                                </div>' . "\r\n" .
              '                                <a' . "\r\n" .
              '                                    href="index.php?prodi=<?= $index ?>"' . "\r\n" .
              '                                    class="w-full inline-flex items-center justify-center gap-1.5 py-2.5 px-4 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/60 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 transition-all"' . "\r\n" .
              '                                >' . "\r\n" .
              '                                    <span>Lihat Rincian</span>' . "\r\n" .
              '                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">' . "\r\n" .
              '                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>' . "\r\n" .
              '                                    </svg>' . "\r\n" .
              '                                </a>' . "\r\n" .
              '                            </div>' . "\r\n" .
              '                        </div>' . "\r\n" .
              '                        <?php endforeach; ?>' . "\r\n" .
              '                    </div>' . "\r\n" .
              '                    <?php endif; ?>' . "\r\n" .
              '                </div>' . "\r\n\r\n";

// Replace the original grid block
$before = substr($content, 0, $posStart);
$after  = substr($content, $posEnd);
$content = $before . $newSection . $after;

file_put_contents($file, $content);
echo "SUCCESS: Patched prodi list view with two separate grids (Aktif + Nonaktif)\n";
echo "File size: " . strlen($content) . " bytes\n";
