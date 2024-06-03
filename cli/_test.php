<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/config.php';


$map1 = [
    12 => [1,8]
];

$data = [
    [
        'id' => 'Vollzeit',
        'cnt' => 1,
        'source_id' => 1,
        'schedule_id' => 1,
        'global_schedule_id' => 1,
        'name' => 'Vollzeit'
    ],
    [
        'id' => 'Teilzeit - Vormittag',
        'cnt' => 5,
        'source_id' => 1,
        'schedule_id' => 5,
        'global_schedule_id' => 5,
        'name' => 'Teilzeit - Vormittag'
    ],
    [
        'id' => 'Teilzeit - Nachmittag',
        'cnt' => 6,
        'source_id' => 1,
        'schedule_id' => 6,
        'global_schedule_id' => 6,
        'name' => 'Teilzeit - Nachmittag'
    ],
    [
        'id' => 'Teilzeit - Abend',
        'cnt' => 7,
        'source_id' => 1,
        'schedule_id' => 7,
        'global_schedule_id' => 7,
        'name' => 'Teilzeit - Abend'
    ],
    [
        'id' => 'Teilzeit - flexibel',
        'cnt' => 8,
        'source_id' => 1,
        'schedule_id' => 8,
        'global_schedule_id' => 8,
        'name' => 'Teilzeit - flexibel'
    ],
    [
        'id' => 'Teilzeit - wtf',
        'cnt' => 8,
        'source_id' => 1,
        'schedule_id' => 8,
        'global_schedule_id' => 8,
        'name' => 'Teilzeit - wtf'
    ],
    [
        'id' => 'Heimarbeit',
        'cnt' => 9,
        'source_id' => 1,
        'schedule_id' => 9,
        'global_schedule_id' => 9,
        'name' => 'Heimarbeit'
    ],
    [
        'id' => 'Vollzeit oder Teilzeit',
        'cnt' => 10,
        'source_id' => 1,
        'schedule_id' => 12,
        'global_schedule_id' => 12,
        'name' => 'Vollzeit oder Teilzeit'
    ]
];


function mergeResults(array $data, array $merge_mapping) {

    $merge_into_tmp = [];
    $merged_data = [];

    foreach($data as $d) {
        $global_schedule_id = $d['global_schedule_id'];
        $tmp_key = $d['source_id'] . '::' . $d['schedule_id'];

        if(array_key_exists($global_schedule_id, $merge_mapping)) {
            foreach($merge_mapping[$global_schedule_id] as $merge_into_global_schedule_id) {
                $merge_into_tmp[] = [
                    'merge_from_id' => $tmp_key,
                    'merge_from_global_schedule_id' => $global_schedule_id,
                    'merge_into_global_schedule_id' => $merge_into_global_schedule_id,
                    'cnt' => $d['cnt']
                ];
            }
        } else {
            $merged_data[] = $d;
        }
    }

    foreach($merge_into_tmp as $m) {
        foreach($merged_data as &$d) {
            if($m['merge_into_global_schedule_id'] === $d['global_schedule_id']) {
                $d['cnt'] += $m['cnt'];
            }
        }
        unset($d);
    }

    return $merged_data;
}

function show(array $data) {
    foreach($data as $d) {
        echo sprintf("%s : %s (%s)", str_pad($d['cnt'], 2, '0', STR_PAD_LEFT), $d['name'], $d['global_schedule_id']) . "\n";
    }
}
show($data);
echo "\n\n";
show(mergeResults($data, $map1));