<?php
function save(array $data, string $fileName)
{
    arsort($data);
    $str = '';
    foreach ($data as $key => $value) {
        $str .= "| $key\t| $value\t|\n";
    }
    file_put_contents("$fileName.txt", $str);
}

$url = "https://api.zp.ru/v1/vacancies?";
$parameters = [
    'geo_id' => 826, // Новосибирск
    'add_date' => date("Y-m-d") // сегодняшняя дата
];

$count = 1;
$offset = 0;
$limit = 25;

$vacancies = [];
$rubrics = [];
$words = [];

while ($offset < $count) {
    $parameters['offset'] = $offset;
    $json = file_get_contents("$url" . http_build_query($parameters));

    if ($json) {
        $data = json_decode($json);
        $count = $data->metadata->resultset->count ?? 0;

        if (is_array($data->vacancies)) {
            foreach ($data->vacancies as $vacancy) {
                $headerWords = explode(' ', $vacancy->header);
                foreach ($headerWords as $word) {
                    if (mb_strlen($word) > 2) { // исключаю предлоги
                        $words[mb_strtolower($word)]++;
                    }
                }

                foreach ($vacancy->rubrics as $rubric) {
                    $rubrics[$rubric->title]++;
                }

                /*
                 * По заданию не было, однако попадались вакансии с абсолютно одинаковым названием,
                 * поэтому решил добавить и этот вариант статистики
                 */
                $vacancies[$vacancy->header]++;
            }
        }
    }
    $offset += $limit;
}

save($vacancies, 'vacancies');
save($rubrics, 'rubrics');
save($words, 'words');
