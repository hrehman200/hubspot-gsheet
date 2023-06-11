<?php
require_once __DIR__ . '/config.php';

date_default_timezone_set('America/Kentucky/Louisville');

// doCurl('events/v3/events?objectType=contact&objectId=144894651');
// exit();

// doCurl('properties/v1/contacts/properties');
// exit();

/*
run a script to add the 'added to list date' to property: added_to_list_date_list_id_63
(you did something like this before)
i only need a one time run of the script b/c i am automating the value going forward
*/

// created a new property that accept datetime, only possible via api
// doCurl('properties/v1/contacts/properties', [
//     "name" => "added_to_list_date_list_id__63",
//     "label" => "added_to_list_date_list_id__63",
//     "description" => "A custom date-time property",
//     "groupName" => "contactinformation",
//     "type" => "datetime",
//     "fieldType" => "datetime",
//     "formField" => true
// ]);

$list_id = 63;

$properties = "&property=email&property=firstname&property=lastname&property=phone";

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

        $added_at = date('m/d/Y, h:i:s A', $contact['addedAt'] / 1000);
        $added_at_wo_time = strtotime(date("Y-m-d", $contact['addedAt'])); // floor($contact['addedAt']/1000/86400)*86400;

        $dt = new \DateTime();
        //$dt->setTimezone(new DateTimeZone("UTC"));
        $dt->setTimestamp($contact['addedAt'] / 1000);
        //$dt->setTime(0, 0, 0);
        $added_at_wo_time = $dt->getTimestamp() * 1000;

        echo "\n $email --- $added_at --- " . $added_at_wo_time;

        $params = [
            'properties' => []
        ];

        $params['properties'][] = ['property' => 'added_to_list_date_list_id__63', 'value' => $contact['addedAt']];

        logg($params);

        $contact_response = doCurl('contacts/v1/contact/vid/' . $contact['vid'] . '/profile', $params);
        logg('contacts/v1/contact/vid/' . $contact['vid'] . '/profile');
        logg($contact_response);
    }

    $offset = $response['vid-offset'];

} while ($response['has-more']);