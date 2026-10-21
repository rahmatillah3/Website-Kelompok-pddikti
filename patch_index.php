<?php
// Script to patch index.php with improved status cells for historis table
$file = 'index.php';
$content = file_get_contents($file);

// Pattern to find: the tbody section with the foreach loop for historis table in prodi detail
$old = '                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm">' . "\r\n" .
       '                                <?php foreach ($periode_historis as $kode => $label): ?>' . "\r\n" .
       '                                <?php $val = $riwayat_mahasiswa[$kode]; ?>' . "\r\n" .
       '                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">' . "\r\n" .
       '                                    <td class="py-3.5 px-4 font-semibold text-slate-900 dark:text-white">' . "\r\n" .
       '                                        <?= htmlspecialchars(substr($kode, 0, 4)) ?>' . "\r\n" .
       '                                    </td>' . "\r\n" .
       '                                    <td class="py-3.5 px-4 text-slate-600 dark:text-slate-300">' . "\r\n" .
       '                                        <?= htmlspecialchars($label) ?>' . "\r\n" .
       '                                    </td>' . "\r\n" .
       '                                    <td class="py-3.5 px-4 text-right font-bold text-brand-600 dark:text-brand-400 text-base">' . "\r\n" .
       '                                        <?= $val === null ? \'-\' : number_format($val, 0, \',\', \'.\') ?>' . "\r\n" .
       '                                    </td>' . "\r\n" .
       '                                    <td class="py-3.5 px-4 text-center">' . "\r\n" .
       '                                        <?php if ($val !== null): ?>' . "\r\n" .
       '                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">' . "\r\n" .
       '                                                Tersedia' . "\r\n" .
       '                                            </span>' . "\r\n" .
       '                                        <?php else: ?>' . "\r\n" .
       '                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400">' . "\r\n" .
       '                                                Tidak Ada' . "\r\n" .
       '                                            </span>' . "\r\n" .
       '                                        <?php endif; ?>' . "\r\n" .
       '                                    </td>' . "\r\n" .
       '                                </tr>' . "\r\n" .
       '                                <?php endforeach; ?>' . "\r\n" .
       '                            </tbody>';

$new = '                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm">' . "\r\n" .
       '                                <?php foreach ($periode_historis as $kode => $label): ?>' . "\r\n" .
       '                                <?php' . "\r\n" .
       '                                    $val = $riwayat_mahasiswa[$kode];' . "\r\n" .
       '                                    $periodeAdaDiJson = in_array($kode, $tersedia_periode);' . "\r\n" .
       '                                ?>' . "\r\n" .
       '                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">' . "\r\n" .
       '                                    <td class="py-3.5 px-4 font-semibold text-slate-900 dark:text-white">' . "\r\n" .
       '                                        <?= htmlspecialchars(substr($kode, 0, 4)) ?>' . "\r\n" .
       '                                    </td>' . "\r\n" .
       '                                    <td class="py-3.5 px-4 text-slate-600 dark:text-slate-300">' . "\r\n" .
       '                                        <?= htmlspecialchars($label) ?>' . "\r\n" .
       '                                        <?php if (!$periodeAdaDiJson): ?>' . "\r\n" .
       '                                            <span class="ml-1 text-[10px] font-bold text-amber-500 dark:text-amber-400">(belum sinkron)</span>' . "\r\n" .
       '                                        <?php endif; ?>' . "\r\n" .
       '                                    </td>' . "\r\n" .
       '                                    <td class="py-3.5 px-4 text-right font-bold text-brand-600 dark:text-brand-400 text-base">' . "\r\n" .
       '                                        <?= $val === null ? \'<span class="text-slate-400 font-normal">-</span>\' : number_format($val, 0, \',\', \'.\') ?>' . "\r\n" .
       '                                    </td>' . "\r\n" .
       '                                    <td class="py-3.5 px-4 text-center">' . "\r\n" .
       '                                        <?php if ($val !== null): ?>' . "\r\n" .
       '                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">' . "\r\n" .
       '                                                &#10003; Tersedia' . "\r\n" .
       '                                            </span>' . "\r\n" .
       '                                        <?php elseif (!$periodeAdaDiJson): ?>' . "\r\n" .
       '                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300" title="Data periode ini tidak tersedia dari API PDDIKTI. Coba klik Refresh PDDIKTI.">' . "\r\n" .
       '                                                &#9888; Tidak Ada di API' . "\r\n" .
       '                                            </span>' . "\r\n" .
       '                                        <?php else: ?>' . "\r\n" .
       '                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400">' . "\r\n" .
       '                                                &mdash; Tidak Ada' . "\r\n" .
       '                                            </span>' . "\r\n" .
       '                                        <?php endif; ?>' . "\r\n" .
       '                                    </td>' . "\r\n" .
       '                                </tr>' . "\r\n" .
       '                                <?php endforeach; ?>' . "\r\n" .
       '                            </tbody>';

if (strpos($content, $old) !== false) {
    $content = str_replace($old, $new, $content);
    file_put_contents($file, $content);
    echo "SUCCESS: Patched tbody section in index.php\n";
} else {
    echo "FAILED: Could not find target string. Trying fallback...\n";
    // Show characters around line 621 context
    $lines = explode("\n", $content);
    echo "Lines 621-625 raw:\n";
    for ($i = 619; $i <= 625; $i++) {
        echo $i . ': ' . bin2hex(substr($lines[$i], 0, 30)) . "\n";
    }
}
