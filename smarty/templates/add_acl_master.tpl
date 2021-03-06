{* header *}
{include file="header.tpl"}
	    {* menu *}
	    {include file="menu.tpl"}

      
    
    <!-- form -->
    <form name="mainform" method="post" action="add_acl_master.php">

    
	   <!--Pagetitle -->
	   <div id="title">ACL Maintenance</div>
           
                 <!-- status msg -->
	    {if $error_msg != ""}
	    	    <div id="errorbox">
	    	    <br/>
	    	        {$error_msg}
	    	        <br/>
	    	    </div>
	    {/if}
	    
            <!--Tablebody-->
	         <div id="wrapper">
	         <table border="0" cellspacing="0" cellpadding="0" id="tablestyle">
		{foreach from=$acl_master_fields item=acl_master_field}
           	<tr>
           	   <td class="label">{$acl_master_field.title}</td>
           	   <td class="child">
           	   	{if $acl_master_field.type == "text"}
           	   	  <input type="{$acl_master_field.type}" name="{$acl_master_field.name}" value="{$acl_master_field.value|escape:"html"}" maxlength="{$acl_master_field.max}"/>
           	   	{elseif $acl_master_field.type == "password"}
           	   	  <input type="{$acl_master_field.type}" name="{$acl_master_field.name}" value="{$acl_master_field.value|escape:"html"}" maxlength="{$acl_master_field.max}"/>
           	   	{elseif $acl_master_field.type == "select"}
			  <select name="{$acl_master_field.name}">
				{html_options options=$acl_master_field.select_options selected=$acl_master_field.select_selected}
			  </select>  
           	   	{elseif $acl_master_field.type == "check"}
           	   	  <input type="checkbox" name="{$acl_master_field.name}" value="1" {if $acl_master_field.value == "1"}checked=true{/if}/>
           	   	{/if}
           	   </td>
           	</tr>
           	{/foreach}
           </table>
	</div>
    		 <!--buttons-->
		      <div id="buttonh">
		        <table border="0" cellspacing="0" cellpadding="0">
		          <tr>
			   <td colspan=20 align="left">
			   <input type="submit" name="btnSubmit" value="Save"/>
		           <input type="reset"  name="btnCancel" value="Clear"/>
			   </td>
		          </tr>
		        </table>
		      </div>
		<!--buttons-->

    
    </form>
    <!-- form -->

    
  </div>
  <!-- CONTENT Ends  -->

<!-- footer -->
{* footer *}
{include file="footer.tpl"}