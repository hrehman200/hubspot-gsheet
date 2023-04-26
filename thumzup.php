<?php
require_once __DIR__ . '/config.php';

$list_id = 360;

$spreadsheetId = '1uY6KepGmV_sLaFUuBRZYuZbeYkG-g17cDCLSy9rIPEc';
$spreadsheetId2 = '17LmF-ivyF6mHz55is-KDrgfaogWhU266nC4nCU_zgTY';

$properties = "&property=email&property=firstname&property=lastname&property=phone";
$properties .= '&property=eftransaction_thumzup';
$properties .= '&property=utm_campaign_thumzup';
$properties .= '&property=utm_content_thumzup';
$properties .= '&property=utm_medium_thumzup';
$properties .= '&property=utm_source_thumzup';
$properties .= '&property=utm_source_thumzup_webinar';
$properties .= '&property=createdate_thumzup';
$properties .= '&property=createdate_thumzup_webinar';
$properties .= '&property=optin_date_thumzup';
$properties .= '&property=optin_date_thumzup_webinar';
$properties .= '&property=thumzup_dalmore_amount';
$properties .= '&property=thumzup_dalmore_investment_step';
$properties .= '&property=thumzup_dalmore_registration_date';
$properties .= '&property=thumzup_dalmore_investment_status';
$properties .= '&property=thumzup_dalmore_signature_date';
// $properties .= '&property=added_to_list_date_list_id_360';

$range = 'Thumzup Leads'; // here we use the name of the Sheet to get all the rows
$response = $service->spreadsheets_values->get($spreadsheetId, $range);
$values = $response->getValues();
$total_rows = count($values);
if ($total_rows > 1) {
    $range = 'Thumzup Leads!A2:Q' . $total_rows;
    $clear = new \Google_Service_Sheets_ClearValuesRequest();
    $service->spreadsheets_values->clear($spreadsheetId, $range, $clear);
}

$response = $service->spreadsheets_values->get($spreadsheetId2, $range);
$values = $response->getValues();
$total_rows = count($values);
if ($total_rows > 1) {
    $range = 'Thumzup Leads!A2:L' . ($total_rows + 1);
    $clear = new \Google_Service_Sheets_ClearValuesRequest();
    $service->spreadsheets_values->clear($spreadsheetId2, $range, $clear);
}

$offset = 0;

$rows = [];
$rows2 = [];

do {
    $response = doCurl('contacts/v1/lists/' . $list_id . '/contacts/all?count=100&formSubmissionMode=newest' . $properties . '&vidOffset=' . $offset);

    foreach ($response['contacts'] as $contact) {
        $contact_props = $contact['properties'];
        $first_name = (string) $contact_props['firstname']['value'];
        $last_name = (string) $contact_props['lastname']['value'];
        $email = (string) $contact_props['email']['value'];
        $phone = (string) $contact_props['phone']['value'];
        $utm_campaign = (string) $contact_props['utm_campaign_thumzup']['value'];
        $utm_source = (string) $contact_props['utm_source_thumzup']['value'];
        if ($utm_source == '') {
            $utm_source = (string) $contact_props['utm_source_thumzup_webinar']['value'];
        }
        $utm_medium = (string) $contact_props['utm_medium_thumzup']['value'];
        $eftransaction_thumzup = (string) $contact_props['eftransaction_thumzup']['value'];
        $utm_content_thumzup = (string) $contact_props['utm_content_thumzup']['value'];
        $createdate_thumzup = (string) $contact_props['createdate_thumzup']['value'];
        $optin_date_thumzup = (string) $contact_props['optin_date_thumzup']['value'];
        if ($optin_date_thumzup == '') {
            $optin_date_thumzup = (string) $contact_props['optin_date_thumzup_webinar']['value'];
            if ($optin_date_thumzup == '') {
                $optin_date_thumzup = (string) $contact_props['createdate_thumzup']['value'];
                if ($optin_date_thumzup == '') {
                    $optin_date_thumzup = (string) $contact_props['createdate_thumzup_webinar']['value'];
                }

                if (is_numeric($optin_date_thumzup)) {
                    $optin_date_thumzup = date('m/d/Y, h:i:s A', $optin_date_thumzup / 1000);
                }
            }
        }
        // finally set same format for date
        $optin_date_thumzup = date('m/d/Y, h:i:s A', strtotime($optin_date_thumzup));
        if(strpos($optin_date_thumzup, '/1970') !== false){
            $optin_date_thumzup = '';
        }

        $added_at = date('m/d/Y, h:i:s A', $contact['addedAt'] / 1000);
        // echo "\n $email --- $added_at --------------------- \n";

        $thumzup_dalmore_amount = (string) $contact_props['thumzup_dalmore_amount']['value'];
        $thumzup_dalmore_investment_step = (string) $contact_props['thumzup_dalmore_investment_step']['value'];
        $thumzup_dalmore_registration_date = (string) $contact_props['thumzup_dalmore_registration_date']['value'];
        $thumzup_dalmore_investment_status = (string) $contact_props['thumzup_dalmore_investment_status']['value'];
        $thumzup_dalmore_signature_date = (string) $contact_props['thumzup_dalmore_signature_date']['value'];

        // $detail_properties = '&property=eftransaction_thumzup';
        // $detail_properties .= '&property=utm_campaign_thumzup';
        // $detail_properties .= '&property=utm_content_thumzup';
        // $detail_properties .= '&property=utm_medium_thumzup';
        // $detail_properties .= '&property=utm_source_thumzup';
        // $detail_properties .= '&property=createdate_thumzup';
        // $detail_properties .= '&property=thumzup_dalmore_amount';
        // $detail_properties .= '&property=thumzup_dalmore_investment_step';
        // $detail_properties .= '&property=thumzup_dalmore_registration_date';
        // $detail_properties .= '&property=thumzup_dalmore_registration_date';
        // $detail_properties .= '&property=thumzup_dalmore_investment_status';
        // $detail_properties .= '&property=thumzup_dalmore_signature_date';

        // $detail_response = doCurl(sprintf('contacts/v1/contact/vid/%d/profile?formSubmissionMode=newest' . $detail_properties, $contact['vid']));
        // $properties = $detail_response['properties'];
        // $utm_campaign = (string) $properties['utm_campaign']['value'];

        // $deals = doCurl(sprintf('crm-associations/v1/associations/%d/HUBSPOT_DEFINED/5', $contact['vid']));

        $rows[] = [
            $first_name,
            $last_name,
            $email,
            format_phone($phone),
            $eftransaction_thumzup,
            $utm_campaign,
            $utm_content_thumzup,
            $utm_medium,
            $utm_source,
            $optin_date_thumzup,
            $thumzup_dalmore_amount,
            $thumzup_dalmore_investment_step,
            $thumzup_dalmore_registration_date,
            $thumzup_dalmore_investment_status,
            $thumzup_dalmore_signature_date,
            $thumzup_dalmore_funded_date,
        ];

        $rows2[] = [
            $first_name,
            $last_name,
            $email,
            format_phone($phone),
            $utm_source,
            $added_at,
            $optin_date_thumzup,
            $thumzup_dalmore_amount,
            $thumzup_dalmore_investment_step,
            $thumzup_dalmore_registration_date,
            $thumzup_dalmore_investment_status,
            $thumzup_dalmore_signature_date,
            $thumzup_dalmore_funded_date,
        ];
    }

    $offset = $response['vid-offset'];

} while ($response['has-more']);


usort($rows, function ($a, $b) {
    $t1 = strtotime($a[9]);
    $t2 = strtotime($b[9]);
    if ($t1 > $t2) {
        return 1;
    } elseif ($t1 < $t2) {
        return -1;
    }
    return 0;
});

usort($rows2, function ($a, $b) {
    if (empty($a[5]) && empty($b[5])) {
        return 0;
    } elseif (empty($a[5])) {
        return -1;
    } elseif (empty($b[5])) {
        return 1;
    } else {

        $a[5] = str_replace(" ", '', $a[5]); // some idiot character being returned from api
        $b[5] = str_replace(" ", '', $b[5]);

        $t1 = strtotime($a[5]);
        $t2 = strtotime($b[5]);
        if (empty($t1) && empty($t2)) {
            return 0;
        } elseif (empty($t1)) {
            return -1;
        } elseif (empty($t2)) {
            return 1;
        } else {
            return $t1 <=> $t2;
        }
    }
});

$valueRange = new \Google_Service_Sheets_ValueRange();
$valueRange->setValues($rows);
$range = 'Thumzup Leads'; // the service will detect the last row of this sheet
$options = ['valueInputOption' => 'USER_ENTERED'];
$service->spreadsheets_values->append($spreadsheetId, $range, $valueRange, $options);

$valueRange = new \Google_Service_Sheets_ValueRange();
$valueRange->setValues($rows2);
$range = 'Thumzup Leads'; // the service will detect the last row of this sheet
$options = ['valueInputOption' => 'USER_ENTERED'];
$service->spreadsheets_values->append($spreadsheetId2, $range, $valueRange, $options);