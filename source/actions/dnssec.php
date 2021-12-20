<?php
/*
 * Enable or disable DNSSEC.
 */

if (!isset($adapter)) {
	return;
}

try {
	$dnssec = $adapter->patch('zones/' . $_GET['zoneid'] . '/dnssec', ['status' => $_GET['do']]);
	$dnssec = json_decode($dnssec->getBody());
} catch (Exception $e) {
	echo '<p class="alert alert-danger" role="alert">' . $e->getMessage() . '</p>';
	return;
}

if ($dnssec->success) {
	echo '<p class="alert alert-success" role="alert">' . l('Success') . ', <a href="?action=security&domain=' . $_GET['domain'] . '&zoneid=' . $_GET['zoneid'] . '">' . l('Go to console') . '</a></p>';
} else {
	echo '<p class="alert alert-danger" role="alert">' . l('Failed') . ', <a href="?action=security&domain=' . $_GET['domain'] . '&zoneid=' . $_GET['zoneid'] . '">' . l('Go to console') . '</a></p>';
}
