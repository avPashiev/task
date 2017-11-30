<?php
$start = microtime(true);

// загрузка данных
$data = load_data();

// построение индексных массивов
$index_email = get_index($data, 1);
$index_card = get_index($data, 2);
$index_phone = get_index($data, 3);

// проход по массиву данных
foreach ($data as $key => $record)
{
    // получение минимального ID из индексов
    $min_id = get_min_id($data, $key, $record, $index_email, $index_card, $index_phone);
    $data[$key][0] = $min_id;
}

save_data('rez.csv', $data);

// время работы скрипта
$time = microtime(true) - $start;
printf('script time %.4F sec.' . PHP_EOL, $time);


/**
 * Формирование индексного массива по одному полю
 */
function get_index($data, $field)
{
    $index=[];
    foreach($data as $k => $v)
    {
        if (isset($index[$v[$field]])) {
            if ($index[$v[$field]] > $k) {
                $index[$v[$field]]=$k;
            }
        } else {
            $index[$v[$field]]=$k;
        }
    }
    return $index;
}

/**
 * Поиск минимального ID во всех индексных файлах
 */
function get_min_id($data, $key, $record, $index_email, $index_card,$index_phone)
{

    $min_id = min($index_email[$record[1]], $index_card[$record[2]], $index_phone[$record[3]]);
    /* если запись с найденым min ID еще не обрабатывалась поиск возможных дублей
        актуально для несортированных по ID данных, напр.
                5,NULL,email5,card4,phone5
                2,NULL,email1,card2,phone2
                4,NULL,email1,card4,phone4
                3,NULL,email7,card7,phone7
                1,NULL,email1,card1,phone1
    */
   while ($data[$min_id][0] == 'NULL') {
           $min_id_p = min($index_email[$data[$min_id][1]], $index_card[$data[$min_id][2]], $index_phone[$data[$min_id][3]]);
           $data[$min_id][0] = $min_id_p;
           $min_id = $min_id_p;
   }
    $min_id = $data[$min_id][0];

    return $min_id;
}

/**
 * Функция загрузки данных из csv файла
 */
function load_data($path = 'data.csv')
{
    $data = [];
    if (($handle = fopen($path, "r")) !== FALSE) {
        while (($record = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $data[array_shift($record)] = $record;
        }
        fclose($handle);
    }
    return $data;
}

/**
 * Вывод результата в csv - файл
 */
function save_data($path, $data)
{
    $fp = fopen($path, 'w');

    foreach ($data as $k => $fields) {
        array_unshift($fields, $k);
        fputcsv($fp, $fields);
    }

    fclose($fp);
}

