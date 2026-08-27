<?php

return [
    'show_warnings' => true,
    'orientation' => 'portrait',
    'default_font' => 'sans-serif',
    'font_dir' => storage_path('fonts/'), 
    'font_cache' => storage_path('fonts/'), 
    'temp_dir' => sys_get_temp_dir(),
    'chroot' => base_path(),
    'pdf_backend' => 'CPDF',
    'default_media_type' => 'screen',
    'default_paper_size' => 'a4',
    'default_font_size' => '10',
    'dpi' => 96,
    'enable_php' => false,
    'enable_javascript' => true,
    'enable_remote' => true,
    'log_output_file' => storage_path('logs/dompdf.html'),
    'font_height_ratio' => 1.1,
    'enable_font_subsetting' => false,
    'enable_html5_parser' => true,
];
