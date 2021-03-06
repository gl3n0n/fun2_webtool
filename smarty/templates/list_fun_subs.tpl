{* header *}
{include file="header.tpl"}
{* menu *}
{include file="menu.tpl"}

	<!-- form -->
	<form name="mainform" method="post" action="list_fun_subs.php">

    
	<!--Pagetitle -->
	<div id="title">Fun Subscribers</div>

	<!-- status msg -->
	{if $error_msg != ""}
		<div id="errorbox">
			<br/>
			{$error_msg}
			<br/>
		</div>
	{else}
		<div id="desc">
			{if $is_searched == "1" }
	                {$list_total|default:"0"} Result(s) found.&nbsp;
			{/if}
		</div>
	{/if}
	    
	<!--Tablebody-->
	<div id="wrapper">
	<table border="0" cellspacing="0" cellpadding="0" id="tablestyle">
		<tr>	
		<td width="150" class="label">Start Date <br>(YYYY-MM-DD)</td>
		<td width="150" class="child"><input type="text" name="q_sdate" maxlength="10" value="{$q_sdate}"/></td>
		<td width="150" class="label">End Date <br>(YYYY-MM-DD)</td>
		<td width="150" class="child"><input type="text" name="q_edate" maxlength="10" value="{$q_edate}"/></td>
		</tr>
		<tr>
		<td width="150" class="label">OFW SIM <br>(Ex. 63917xxxxxxx)</td>
		<td class="child"><input type="text" name="q_msisdn" maxlength="20" value="{$q_msisdn}"/></td>
		<td width="150" class="label">LOCAL SIM <br>(Ex. 63917xxxxxxx)</td>
		<td class="child"><input type="text" name="q_linkto" maxlength="20" value="{$q_linkto}"/></td>
<!--
		<td width="150" class="label">Customer Type</td>
	                   		<td width="150" class='label'>
			<select name="q_service" id="q_service">
				{html_options options=$soptcustomer selected='%'}
			</select>  
		</td>
-->
		</tr>
		<tr>	
		<td width="150" class="label">Status</td>
		<td class="child">
			<select name="q_status" id="q_status">
				{html_options options=$soptstatus selected=$q_status}
			</select>  
		</td>
		<td width="100" class="label">&nbsp;</td>
		<td colspan="3" class="child">
			<input type="submit" name="btnSubmit" value="Search" />
			&nbsp;
			{if $error_msg != ""}
				<input type="button" name="btnReset" value="Clear" onClick="clearOnListErrForm();"/></td>
			{elseif $is_searched == "1"}
				<input type="button" name="btnReset" value="Clear" onClick="clearOnSearchForm();"/></td>
			{else}
				<input type="reset" name="btnReset" value="Clear"/></td>
			{/if}
		</td>
		</tr>
	</table>
	</div>

	<!--Tablebody-->

	<div id="wrapper">

		{if $is_searched == "1" }

		<table border="0" cellspacing="0" cellpadding="0" id="tablestyle" width="100%">
			<tr>	
			<td width="100" class="label">OFW SIM</td>
			<td width="100" class="label">LOCAL SIM</td>
			<td width="150" class="label">Activation Date</td>
			<td width="150" class="label">Deactivation Date</td>
			<td width="50"  class="label">Status</td>
			<td width="100" class="label">Last Activity Date</td>
			<td width="150" class="label">Expiry Notification</td>
			<td width="80"  class="label">CS Username</td>
			</tr>
           		{foreach from=$list_data item=ldata}
				<tr >
				<td class="child" width="100">&nbsp;{$ldata.msisdn}</td>
				<td class="child" width="100" >&nbsp;{$ldata.link_to}</td>
				<td class="child" width="150">&nbsp;{$ldata.activation_dt}</td>
				<td class="child" width="150">&nbsp;{$ldata.deactivation_dt}</td>
				<td class="child" width="50" >&nbsp;{$ldata.status}</td>
				<td class="child" width="100">&nbsp;{$ldata.ods_last_chk_dt}</td>
				<td class="child" width="150">&nbsp;{$ldata.notify_date}</td>
				<td class="child" width="80" >&nbsp;{$ldata.cs_username}</td>
				</tr>
           		{/foreach}
                	
			<tr>
			<td colspan=20 align="left" class="child">
			{$list_nav|default:""}&nbsp;
			</td>
           		</tr>
                	
			{if $list_total == ".1" }
			<tr>
			<td colspan=20 align="left" class="child">
			<a href="dl.php?muid={php}echo get_uid();{/php}&csv={$q_csv}">Download CSV</a>
			</td>
			</tr>
			{/if}

		</table>

		{/if}

	</div>
    
    </form>
    <!-- form -->

    
  <!-- CONTENT Ends  -->

<!-- footer -->
{* footer *}
{include file="footer.tpl"}
