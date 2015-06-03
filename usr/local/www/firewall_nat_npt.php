<?php
/* $Id$ */
/*
	firewall_nat_npt.php
	part of pfSense (https://www.pfsense.org)

	Copyright (C) 2011 Seth Mos <seth.mos@dds.nl>.
	All rights reserved.

	Copyright (C) 2013-2015 Electric Sheep Fencing, LP
	All rights reserved.

	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:

	1. Redistributions of source code must retain the above copyright notice,
	   this list of conditions and the following disclaimer.

	2. Redistributions in binary form must reproduce the above copyright
	   notice, this list of conditions and the following disclaimer in the
	   documentation and/or other materials provided with the distribution.

	THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
	INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
	AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
	AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
	OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
	SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
	INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
	CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
	ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
	POSSIBILITY OF SUCH DAMAGE.
*/
/*
	pfSense_MODULE: nat
*/

##|+PRIV
##|*IDENT=page-firewall-nat-npt
##|*NAME=Firewall: NAT: NPT page
##|*DESCR=Allow access to the 'Firewall: NAT: NPT' page.
##|*MATCH=firewall_nat_npt.php*
##|-PRIV

require("guiconfig.inc");
require_once("functions.inc");
require_once("filter.inc");
require_once("shaper.inc");

if (!is_array($config['nat']['npt']))
	$config['nat']['npt'] = array();

$a_npt = &$config['nat']['npt'];

if ($_POST) {
	$pconfig = $_POST;

	if ($_POST['apply']) {
		$retval = 0;
		$retval |= filter_configure();
		$savemsg = get_std_save_message($retval);

		if ($retval == 0) {
			clear_subsystem_dirty('natconf');
			clear_subsystem_dirty('filter');
		}
	}
}

if ($_GET['act'] == "del") {
	if ($a_npt[$_GET['id']]) {
		unset($a_npt[$_GET['id']]);

		if (write_config())
			mark_subsystem_dirty('natconf');

		header("Location: firewall_nat_npt.php");
		exit;
	}
}

$pgtitle = array(gettext("Firewall"),gettext("NAT"),gettext("NPt"));
include("head.inc");

if ($savemsg)
	print_info_box($savemsg, 'success');

if (is_subsystem_dirty('natconf'))
	print_info_box_np(gettext("The NAT configuration has been changed") . ".<br />" . gettext("You must apply the changes in order for them to take effect."));

$tab_array = array();
$tab_array[] = array(gettext("Port Forward"), false, "firewall_nat.php");
$tab_array[] = array(gettext("1:1"), false, "firewall_nat_1to1.php");
$tab_array[] = array(gettext("Outbound"), false, "firewall_nat_out.php");
$tab_array[] = array(gettext("NPt"), true, "firewall_nat_npt.php");
display_top_tabs($tab_array);
?>

<form action="firewall_nat_npt.php" method="post">
	<div class="panel panel-default">
		<div class="panel-body table responsive">
			<table class="table table-striped table-hover table-condensed">
				<thead>
					<tr>
						<th><?=gettext("Interface")?></th>
						<th><?=gettext("External Prefix")?></th>
						<th><?=gettext("Internal prefix")?></th>
						<th><?=gettext("Description")?></th>
						<th><!-- Buttons --></th>
					</tr>
				</thead>
				<tbody>
<?php

$i = 0;
foreach ($a_npt as $natent):

	// Disabled interfaces display in light gray
	$style = isset($natent['disabled']) ? 'style="color:#B0B0B0"':'';
?>
					<tr <?=$style?>>
						<td>
<?php
	if (!$natent['interface'])
		print(htmlspecialchars(convert_friendly_interface_to_friendly_descr("wan")));
	else
		print(htmlspecialchars(convert_friendly_interface_to_friendly_descr($natent['interface'])));
?>
						</td>
<?php
	$source_net = pprint_address($natent['source']);
	$source_cidr = strstr($source_net, '/');
	$destination_net = pprint_address($natent['destination']);
	$destination_cidr = strstr($destination_net, '/');
?>
						<td>
							<?=$destination_net?>
						</td>
						<td>
							<?=$source_net?>
						</td>
						<td >
							<?=htmlspecialchars($natent['descr'])?>
						</td>
						<td>
							<a href="firewall_nat_npt_edit.php?id=<?=$i?>" class="btn btn-xs btn-info"><?=gettext("Edit")?></a>
							<a href="firewall_nat_npt.php?act=del&amp;id=<?=$i?>" class="btn btn-xs btn-danger"><?=gettext("Delete")?></a>
						</td>
					</tr>
<?php
	$i++;
endforeach;
?>
				</tbody>
			</table>

			<?=($i > 0) ? sprintf(gettext('Rows shown in %sgray%s are disabled'), '<span style="color:#B0B0B0">', '</span>'):''?>

			<nav class="action-buttons">
				<a href="firewall_nat_npt_edit.php" class="btn btn-sm btn-success"><?=gettext("Add rule")?></a>
			</nav>

		</div>
	</div>
</form>
<?php

include("foot.inc");