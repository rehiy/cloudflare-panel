<?php
/*
 * Enable or disable DNSSEC.
 */

if (!isset($adapter)) {
	return;
}

$zoneId = $_GET['zoneid'] ?? '';
$zoneName = $_GET['domain'] ?? '';

$status = $_GET['do'] ?? '';

try {
	$dnssec = $adapter->patch('zones/' . $zoneId . '/dnssec', ['status' => $status]);
	$dnssec = json_decode($dnssec->getBody());
} catch (Exception $e) {
	echo '<p class="alert alert-danger" role="alert">' . $e->getMessage() . '</p>';
	return;
}

if ($dnssec->success) {
	echo '<p class="alert alert-success" role="alert">' . l('Success') . ', <a href="?action=security&domain=' . $zoneName . '&zoneid=' . $zoneId . '">' . l('Go to console') . '</a></p>';
} else {
	echo '<p class="alert alert-danger" role="alert">' . l('Failed') . ', <a href="?action=security&domain=' . $zoneName . '&zoneid=' . $zoneId . '">' . l('Go to console') . '</a></p>';
}
