<?php
// Script to patch dashboard summary cards section in index.php
$file = 'index.php';
$content = file_get_contents($file);

// OLD: 4-card grid (Total Prodi, Mahasiswa, Dosen, Akreditasi Unggul)
$old = '                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">' . "\r\n" .
       '                        <!-- Card 1: Total Prodi -->' . "\r\n" .
       '                        <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-lg transition-all hover:-translate-y-1">' . "\r\n" .
       '                            <div class="flex items-center justify-between mb-4">' . "\r\n" .
       '                                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Program Studi</span>' . "\r\n" .
       '                                <div class="w-12 h-12 rounded-2xl bg-brand-50 dark:bg-brand-950/70 text-brand-600 dark:text-brand-400 flex items-center justify-center">' . "\r\n" .
       '                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">' . "\r\n" .
       '                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>' . "\r\n" .
       '                                    </svg>' . "\r\n" .
       '                                </div>' . "\r\n" .
       '                            </div>' . "\r\n" .
       '                            <div class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight">' . "\r\n" .
       '                                <?= $total_prodi ?>' . "\r\n" .
       '                            </div>' . "\r\n" .
       '                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 flex items-center gap-1.5">' . "\r\n" .
       '                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>' . "\r\n" .
       '                                Tersedia pada jenjang D2, D3, D4, & S2' . "\r\n" .
       '                            </p>' . "\r\n" .
       '                        </div>';

$new = '                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">' . "\r\n" .
       '                        <!-- Card 1: Total Prodi (aktif + nonaktif) -->' . "\r\n" .
       '                        <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-lg transition-all hover:-translate-y-1">' . "\r\n" .
       '                            <div class="flex items-center justify-between mb-4">' . "\r\n" .
       '                                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Program Studi</span>' . "\r\n" .
       '                                <div class="w-12 h-12 rounded-2xl bg-brand-50 dark:bg-brand-950/70 text-brand-600 dark:text-brand-400 flex items-center justify-center">' . "\r\n" .
       '                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">' . "\r\n" .
       '                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>' . "\r\n" .
       '                                    </svg>' . "\r\n" .
       '                                </div>' . "\r\n" .
       '                            </div>' . "\r\n" .
       '                            <div class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight">' . "\r\n" .
       '                                <?= $total_prodi ?>' . "\r\n" .
       '                            </div>' . "\r\n" .
       '                            <div class="mt-2 flex items-center gap-3 text-xs">' . "\r\n" .
       '                                <span class="flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-semibold">' . "\r\n" .
       '                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>' . "\r\n" .
       '                                    <?= $total_prodi_aktif ?> Aktif' . "\r\n" .
       '                                </span>' . "\r\n" .
       '                                <span class="flex items-center gap-1 text-rose-600 dark:text-rose-400 font-semibold">' . "\r\n" .
       '                                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>' . "\r\n" .
       '                                    <?= $total_prodi_nonaktif ?> Non-Aktif' . "\r\n" .
       '                                </span>' . "\r\n" .
       '                            </div>' . "\r\n" .
       '                        </div>';

if (strpos($content, $old) !== false) {
    $content = str_replace($old, $new, $content);
    file_put_contents($file, $content);
    echo "SUCCESS: Patched dashboard cards section (Card 1 - Total Prodi)\n";
} else {
    echo "FAILED: Target not found in Card 1\n";
}

// Now patch the Daftar Program Studi section to show two tables
// First, find the grid/cards section for prodi list view and add status column + two tables
echo "Done.\n";
