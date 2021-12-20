<?php
/*
 * Edit a record
 */

if (!isset($adapter)) {
	exit;
}

$dns = new \Cloudflare\API\Endpoints\DNS($adapter);
$dns_details = $dns->getRecordDetails($_GET['zoneid'], $_GET['recordid']);

if (isset($_POST['submit'])) {
	if (isset($_POST['proxied']) && $_POST['proxied'] == 'true') {
		$_POST['proxied'] = true;
	} else {
		$_POST['proxied'] = false;
	}
	$_POST['ttl'] = intval($_POST['ttl']);
	$_POST['type'] = $dns_details->type;
	if (isset($_POST['priority']) && $_POST['type'] == 'MX') {
		$_POST['priority'] = intval($_POST['priority']);
	} else {
		$_POST['priority'] = 10;
	}
	if (!isset($_POST['content'])) {
		$_POST['content'] = "";
	}

	$options = [
		'type' => $dns_details->type,
		'name' => $_POST['name'],
		'content' => $_POST['content'],
		'ttl' => intval($_POST['ttl']),
		'priority' => $_POST['priority'],
		'proxied' => $_POST['proxied']
	];

	include __DIR__ . '/record_data.php';

	try {
		if ($dns->updateRecordDetails($_GET['zoneid'], $_GET['recordid'], $options)) {
			echo '<p class="alert alert-success" role="alert">' . l('Success') . '</p>';
		} else {
			echo '<p class="alert alert-danger" role="alert">' . l('Failed') . '</p>';
		}
	} catch (Exception $e) {
		echo '<p class="alert alert-danger" role="alert">' . l('Failed') . '</p>';
		echo '<p class="alert alert-warning" role="alert">' . $e->getMessage() . '</p>';
	}
}
?>

<strong><?php echo '<h1 class="h5"><a href="?action=zone&domain=' . $_GET['domain'] . '&zoneid=' . $_GET['zoneid'] . '">&lt;- ' . l('Back') . '</a></h1>'; ?></strong>

<hr>

<form method="POST" action="">
	<fieldset>
		<legend><?php echo l('Edit DNS Record'); ?></legend>
		<div class="form-group">
			<label for="name"><?php echo l('Record Name (e.g. “@”, “www”, etc.)'); ?></label>
			<input type="text" name="name" id="name" value="<?php echo htmlspecialchars($dns_details->name); ?>" class="form-control">
		</div>
		<div class="form-group">
			<label for="type"><?php echo l('Record Type'); ?></label>
			<select name="type" id="type" disabled="disabled" class="form-control">
				<option value="<?php echo $dns_details->type; ?>"><?php echo $dns_details->type; ?></option>
			</select>
		</div>

		<?php if ($dns_details->type == 'CAA') { ?>
			<div class="form-group">
				<label for="data_tag"><?php echo l('Tag'); ?></label>
				<select name="data_tag" id="data_tag" class="form-control" data-selected="<?php echo $dns_details->data->tag ?>">
					<option value="issue"><?php echo l('Only allow specific hostnames') ?></option>
					<option value="issuewild"><?php echo l('Only allow wildcards') ?></option>
					<option value="iodef"><?php echo l('Send violation reports to URL (http:, https:, or mailto:)') ?></option>
				</select>
			</div>
			<div class="form-group">
				<label for="data_value"><?php echo l('Value'); ?></label>
				<input type="text" name="data_value" id="data_value" value="<?php echo htmlspecialchars($dns_details->data->value); ?>" class="form-control">
			</div>
			<input type="hidden" name="data_flags" value="0">
		<?php } elseif ($dns_details->type == 'SRV') { ?>
			<div class="form-group">
				<label for="srv_service"><?php echo l('Service'); ?></label>
				<input type="text" name="srv_service" id="srv_service" value="<?php echo $dns_details->data->service ?>" class="form-control">
			</div>
			<div class="form-group">
				<label for="srv_proto"><?php echo l('Proto'); ?></label>
				<select name="srv_proto" id="srv_proto" class="form-control" data-selected="<?php echo $dns_details->data->proto ?>">
					<option value="_tcp">TCP</option>
					<option value="_udp">UDP</option>
					<option value="_tls">TLS</option>
				</select>
			</div>
			<div class="form-group">
				<label for="srv_name"><?php echo l('Name'); ?></label>
				<input type="text" name="srv_name" id="srv_name" value="<?php echo $dns_details->data->name ?>" class="form-control">
			</div>
			<div class="form-group">
				<label for="srv_priority"><?php echo l('Priority'); ?></label>
				<input type="text" name="srv_priority" id="srv_priority" value="<?php echo $dns_details->data->priority ?>" class="form-control">
			</div>
			<div class="form-group">
				<label for="srv_weight"><?php echo l('Weight'); ?></label>
				<input type="text" name="srv_weight" id="srv_weight" value="<?php echo $dns_details->data->weight ?>" class="form-control">
			</div>
			<div class="form-group">
				<label for="srv_port"><?php echo l('Port'); ?></label>
				<input type="text" name="srv_port" id="srv_port" value="<?php echo $dns_details->data->port ?>" class="form-control">
			</div>
			<div class="form-group">
				<label for="srv_target"><?php echo l('Target'); ?></label>
				<input type="text" name="srv_target" id="srv_target" value="<?php echo $dns_details->data->target ?>" class="form-control">
			</div>
		<?php } else { ?>
			<div class="form-group">
				<label for="doc-ta-1"><?php echo l('Record Content'); ?></label>
				<textarea name="content" rows="5" id="doc-ta-1" class="form-control"><?php echo htmlspecialchars($dns_details->content); ?></textarea>
			</div>
			<?php if ($dns_details->type == 'MX' || $dns_details->type == 'SRV') { ?>
				<div class="form-group">
					<label for="priority"><?php echo l('Priority'); ?></label>
					<input type="number" name="priority" id="priority" step="1" min="1" value="<?php echo $dns_details->priority; ?>" class="form-control">
				</div>
			<?php } ?>
		<?php } ?>

		<div class="form-group">
			<label for="ttl">TTL</label>
			<select name="ttl" id="ttl" class="form-control">
				<?php
				foreach ($ttl_translate as $_ttl => $_ttl_name) {
					echo '<option value="' . $_ttl . '">' . $_ttl_name . '</option>';
				}
				?>
			</select>
		</div>
		<?php if ($dns_details->proxiable) { ?>
			<div class="form-group">
				<label for="proxied">CDN</label>
				<select name="proxied" id="proxied" class="form-control">
					<option value="true" <?php echo $dns_details->proxied ? 'selected="selected"' : ''; ?>><?php echo l('On'); ?></option>
					<option value="false" <?php echo $dns_details->proxied ? '' : 'selected="selected"';  ?>><?php echo l('Off'); ?></option>
				</select>
			</div>
		<?php } ?>

		<button type="submit" name="submit" class="btn btn-primary"><?php echo l('Submit'); ?></button>
	</fieldset>
</form>