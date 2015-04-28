<?php
/*
	diag_dump_states_sources.php
	Copyright (C) 2005 Colin Smith
	Copyright (C) 2005-2009 Scott Ullrich
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
	pfSense_BUILDER_BINARIES:	/sbin/pfctl
	pfSense_MODULE: filter
*/

##|+PRIV
##|*IDENT=page-diagnostics-showstates
##|*NAME=Diagnostics: Show States page
##|*DESCR=Allow access to the 'Diagnostics: Show States' page.
##|*MATCH=diag_dump_states.php*
##|-PRIV

require_once("guiconfig.inc");

/* handle AJAX operations */
if($_GET['action']) {
	if($_GET['action'] == "remove") {
		if (is_ipaddr($_GET['srcip']) and is_ipaddr($_GET['dstip'])) {
			$retval = mwexec("/sbin/pfctl -K " . escapeshellarg($_GET['srcip']) . " -K " . escapeshellarg($_GET['dstip']));
			echo htmlentities("|{$_GET['srcip']}|{$_GET['dstip']}|{$retval}|");
		} else {
			echo gettext("invalid input");
		}
		exit;
	}
}

/* get our states */
if($_GET['filter']) {
	exec("/sbin/pfctl -s Sources | grep " . escapeshellarg(htmlspecialchars($_GET['filter'])), $sources);
}
else {
	exec("/sbin/pfctl -s Sources", $sources);
}

$pgtitle = array(gettext("Diagnostics"),gettext("Show Source Tracking"));
include("head.inc");

?>

<form action="diag_dump_states_sources.php" method="get" name="iform">

<script type="text/javascript">
//<![CDATA[
	function removeSource(srcip, dstip) {
		var busy = function(index,icon) {
			jQuery(icon).bind("onclick","");
			jQuery(icon).attr('src',jQuery(icon).attr('src').replace("\.gif", "_d.gif"));
			jQuery(icon).css("cursor","wait");
		}

		jQuery('img[name="i:' + srcip + ":" + dstip + '"]').each(busy);

		jQuery.ajax(
			"<?=$_SERVER['SCRIPT_NAME']?>" +
				"?action=remove&srcip=" + srcip + "&dstip=" + dstip,
			{ type: "get", complete: removeComplete }
		);
	}

	function removeComplete(req) {
		var values = req.responseText.split("|");
		if(values[3] != "0") {
			alert('<?=gettext("An error occurred.")?>');
			return;
		}

		jQuery('tr[name="r:' + values[1] + ":" + values[2] + '"]').each(
			function(index,row) { jQuery(row).fadeOut(1000); }
		);
	}
//]]>
</script>


<div class="panel panel-default">

<?php
	$tab_array = array();
	$tab_array[] = array(gettext("States"), false, "diag_dump_states.php");
	$tab_array[] = array(gettext("Source Tracking"), true, "diag_dump_states_sources.php");
	$tab_array[] = array(gettext("Reset States"), false, "diag_resetstate.php");
	display_top_tabs($tab_array);
?>

<!-- Start of tab content -->
	<br />
	<form action="<?=$_SERVER['SCRIPT_NAME']?>" method="get">
		<div class="pull-right">
			<?=gettext("Filter expression:")?>
			<input type="text" name="filter" class="formfld search" value="<?=htmlspecialchars($_GET['filter'])?>" size="30" />
			<input type="submit" class="btn btn-primary btn-sm formbtn" value="<?=gettext("Filter")?>" />
			&nbsp;
		</div>
	</form>
	<br>
	<div class="responsive">
	<table class="table table-striped table-hover table-compact">
		<thead>
			<tr>
				<th class="listhdrr" width="40%"><?=gettext("Source -> Destination")?></th>
				<th class="listhdrr" width="15%"><?=gettext("# States")?></th>
				<th class="listhdrr" width="15%"><?=gettext("# Connections")?></th>
				<th class="listhdr" width="15%"><?=gettext("Rate")?></th>
				<th class="list sort_ignore" width="1%"></th>
			</tr>
		</thead>
		<tbody>

<?php
$row = 0;
if(count($sources) > 0) {
	foreach($sources as $line) {
		if($row >= 1000)
			break;

		// 192.168.20.2 -> 216.252.56.1 ( states 10, connections 0, rate 0.0/0s )

		$source_split = "";
		preg_match("/(.*)\s\(\sstates\s(.*),\sconnections\s(.*),\srate\s(.*)\s\)/", $line, $source_split);
		list($all, $info, $numstates, $numconnections, $rate) = $source_split;

		$source_split = "";
		preg_match("/(.*)\s\<?-\>?\s(.*)/", $info, $source_split);
		list($all, $srcip, $dstip) = $source_split;

?>
        	<tr name='r:<?="{$srcip}:{$dstip}" ?>'>
        		<td class='listlr'><?=$info?></td>
        		<td class='listr'><?=$numstates?></td>
        		<td class='listr'><?=$numconnections?></td>
        		<td class='listr'><?=$rate?></td>
        		<td class='list'>
            		<a class="btn btn-xs btn-default"
            			onclick="removeSource('<?= $srcip ?>', '<?= $dstip ?>');" name="i:<?= $srcip ?>:<?= $dstip ?>"
            			title="<?= gettext('Remove all source tracking entries from ') ?><?= $srcip ?><?= gettext(' to ') ?><?= $dstip ?>" alt="">Remove</a>
        		</td>
        	</tr>
<?php
		$row++;
	}
}
else {
?>
	        <tr>
        		<td class="list" colspan="4" align="center" valign="top">
        		<?= gettext("No source tracking entries were found.") ?>
        		</td>
        	</tr>
<?php
}
?>

<!-- End of tab content -->

		</tbody>
	</table>
    </div>
</div>
<?php include("foot.inc"); ?>