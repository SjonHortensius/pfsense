<?php
/* $Id$ */
/*
	diag_resetstate.php
	Copyright (C) 2013-2015 Electric Sheep Fencing, LP
	Copyright (C) 2004-2009 Scott Ullrich
	All rights reserved.

	originally part of m0n0wall (http://m0n0.ch/wall)
	Copyright (C) 2003-2004 Manuel Kasper <mk@neon1.net>.
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
	pfSense_MODULE: filter
*/

##|+PRIV
##|*IDENT=page-diagnostics-resetstate
##|*NAME=Diagnostics: Reset state page
##|*DESCR=Allow access to the 'Diagnostics: Reset state' page.
##|*MATCH=diag_resetstate.php*
##|-PRIV

define("DEBUG", true);  // Displays "success" messages, but does not call the functions that reset the firewall state tables

require("guiconfig.inc");
require_once("filter.inc");

if ($_POST) {
	$savemsg = "";

	if ($_POST['statetable']) {
		if(!DEBUG)
		  filter_flush_state_table();

		if ($savemsg)
			$savemsg .= " ";

		$savemsg .= gettext("The state table has been flushed successfully.");
	}

	if ($_POST['sourcetracking']) {
		if(!DEBUG)
		  mwexec("/sbin/pfctl -F Sources");

		if ($savemsg)
			$savemsg .= " <br />";

		$savemsg .= gettext("The source tracking table has been flushed successfully.");
	}
}

$pgtitle = array(gettext("Diagnostics"), gettext("Reset state"));
include("head.inc");

if ($input_errors) print_input_errors($input_errors);if ($savemsg)
	print('<div class="alert alert-success" role="alert">'.$savemsg.'</div>');
?>

<div class="panel panel-default">
<?php
	$tab_array = array();
	$tab_array[] = array(gettext("States"), false, "diag_dump_states.php");
	if (isset($config['system']['lb_use_sticky']))
		$tab_array[] = array(gettext("Source Tracking"), false, "diag_dump_states_sources.php");
		$tab_array[] = array(gettext("Reset States"), true, "diag_resetstate.php");
		display_top_tabs($tab_array);
?>

    <form action="diag_resetstate.php" method="post" name="iform" id="iform">
		<div class="panel-body">
			<div class="checkbox"><label><input name="statetable"  type="checkbox" id="statetable" value="yes" checked="checked">
				<?= gettext("Reset the firewall state table"); ?></label>
			</div>

		    <?=gettext( "Resetting the state tables will remove all entries from the corresponding tables. This means that all open connections " .
					    "will be broken and will have to be re-established. This may be necessary after making substantial changes to the " .
					    "firewall and/or NAT rules, especially if there are IP protocol mappings (e.g. for PPTP or IPv6) with open connections."); ?>
		    <br /><br />
		    <?=gettext( "The firewall will normally leave the state tables intact when changing rules."); ?>
		    <br /><br />
		    <?=gettext( "NOTE: If you reset the firewall state table, the browser session may appear to be hung after clicking &quot;Reset&quot;. " .
					    "Simply refresh the page to continue."); ?>
		    </div>
    	</div>
		
<?php 
        if (isset($config['system']['lb_use_sticky']) || DEBUG): 
?>
		<div class="panel panel-default">
			<div class="panel-body">
				<div class="checkbox"><label><input name="sourcetracking" class="checkbox" type="checkbox" id="sourcetracking" value="yes" checked="checked">
					 <?= gettext("Firewall Source Tracking"); ?><br />
				</div>

			    <?=gettext( "Resetting the source tracking table will remove all source/destination associations. " .
						    "This means that the \"sticky\" source/destination association " .
						    "will be cleared for all clients."); ?>
			    <br /><br />
			    <?=gettext("This does not clear active connection states, only source tracking."); ?>
			    <br />
			</div>
		</div>
		<?php endif; ?>

	<input name="Submit" type="submit" class="btn btn-danger" value="<?=gettext("Reset"); ?>" />
</form>
</div>

<?php include("foot.inc"); ?>
