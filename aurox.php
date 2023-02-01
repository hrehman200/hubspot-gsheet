<?php
require_once __DIR__ . '/config.php';

$list_id = 361;

$spreadsheetId = '14KacnW6_1cu8kaiTZLAi_pXVSPUUgURRiepHOFns3gg';
$spreadsheetId2 = '1WJy0Z3K282T136ubm2M35gxDdkbbYmFLpBYhX2BdZTI';
$sheet_name = 'Aurox Leads';
$sheet_range = 'A2:J';
$sheet_range2 = 'A2:F';

$properties = "&property=email&property=firstname&property=lastname&property=phone";
$properties .= '&property=eftransaction_aurox';
$properties .= '&property=utm_campaign_aurox';
$properties .= '&property=utm_content_aurox';
$properties .= '&property=utm_medium_aurox';
$properties .= '&property=utm_source_aurox';
$properties .= '&property=createdate_aurox';

$range = $sheet_name; // here we use the name of the Sheet to get all the rows
$response = $service->spreadsheets_values->get($spreadsheetId, $range);
$values = $response->getValues();
$total_rows = count($values);
if ($total_rows > 1) {
    $range = $sheet_name . '!' . $sheet_range . $total_rows;
    $clear = new \Google_Service_Sheets_ClearValuesRequest();
    $service->spreadsheets_values->clear($spreadsheetId, $range, $clear);
}

$response = $service->spreadsheets_values->get($spreadsheetId2, $range);
$values = $response->getValues();
$total_rows = count($values);
if ($total_rows > 1) {
    $range = $sheet_name . '!' . $sheet_range2 . ($total_rows + 1);
    $clear = new \Google_Service_Sheets_ClearValuesRequest();
    $service->spreadsheets_values->clear($spreadsheetId2, $range, $clear);
}

$offset = 0;

do {
    $response = doCurl('contacts/v1/lists/' . $list_id . '/contacts/all?count=100&formSubmissionMode=newest' . $properties . '&vidoffset=' . $offset);

    $rows = [];
    $rows2 = [];
    foreach ($response['contacts'] as $contact) {
        $contact_props = $contact['properties'];
        $first_name = (string) $contact_props['firstname']['value'];
        $last_name = (string) $contact_props['lastname']['value'];
        $email = (string) $contact_props['email']['value'];
        $phone = (string) $contact_props['phone']['value'];

        $eftransaction_aurox = (string) $contact_props['eftransaction_aurox']['value'];
        $utm_campaign = (string) $contact_props['utm_campaign_aurox']['value'];
        $utm_source = (string) $contact_props['utm_source_aurox']['value'];
        $utm_medium = (string) $contact_props['utm_medium_aurox']['value'];
        $utm_content_aurox = (string) $contact_props['utm_content_aurox']['value'];
        $createdate_aurox = (string) $contact_props['createdate_aurox']['value'];

        $rows[] = [
            $first_name,
            $last_name,
            $email,
            $phone,
            $eftransaction_aurox,
            $utm_campaign,
            $utm_content_aurox,
            $utm_medium,
            $utm_source,
            $createdate_aurox ? date('Y-m-d', $createdate_aurox / 1000) : ''
        ];

        $rows2[] = [
            $first_name,
            $last_name,
            $email,
            $phone,
            $utm_source,
            $createdate_aurox ? date('Y-m-d', $createdate_aurox / 1000) : ''
        ];
    }

    $valueRange = new \Google_Service_Sheets_ValueRange();
    $valueRange->setValues($rows);
    $range = $sheet_name; // the service will detect the last row of this sheet
    $options = ['valueInputOption' => 'USER_ENTERED'];
    $service->spreadsheets_values->append($spreadsheetId, $range, $valueRange, $options);

    $valueRange = new \Google_Service_Sheets_ValueRange();
    $valueRange->setValues($rows2);
    $range = $sheet_name; // the service will detect the last row of this sheet
    $options = ['valueInputOption' => 'USER_ENTERED'];
    $service->spreadsheets_values->append($spreadsheetId2, $range, $valueRange, $options);

    $offset = $response['vid-offset'];

} while ($response['has-more']);