<?php
/**
#----------------------------------------------------------------------------------
#	@Filename	:	HlrSrvr.class.php
#	@Description:	encapsulator for table HLR_SERVERS
#
#
#	@Date		:	2009-05-25
#	@Ver		:	ver1.1
#	@Author		:	yiucon
#
#
#  @Modified Date:
#  @Modified By  :
#  @Modified Desc:
#
#----------------------------------------------------------------------------------
 **/

//include
include_once("Pager/Pager.php");
define('CSV_HEADER_HLRSVR_REPORT',  "SERVER_IP, SERVER_PORT, SERVER_NAME, USERNAME, PASSWORD, TIMEOUT, RETRY_CNT, STATUS, CREATED BY, DATE, MODIFIED BY, DATE");

//class
class HlrSrvr
{

	//constructor
	function HlrSrvr()
	{

	}

	//init
	function Get($id='')
	{
		//globals here
		global $gEventLogger;

		//db
		$myOraDb =& myOraDb::GetInstance(DB_USER,DB_PASS,DB_NAME);
		$conn    = $myOraDb->dbConnection();

		//format the insert-parameters
		$id      = intval (trim($id));

		//format the insert-parameters
		$sql     = " SELECT  id, server_ip, server_port, server_name, username, password, timeout, retry_cnt, status FROM HLR_SERVERS WHERE ID=$id ";

		//Parse SQL passed
		$stmt    = OCIParse($conn,$sql);

		//total
		$totalrows = 0;

		$this->debug("Open() : [$sql / $stmt ]");

		//bind
		OCIDefineByName($stmt, 'ID'         , $P_ID          );
		OCIDefineByName($stmt, 'SERVER_IP'  , $P_SERVER_IP   );
		OCIDefineByName($stmt, 'SERVER_PORT', $P_SERVER_PORT );
		OCIDefineByName($stmt, 'SERVER_NAME', $P_SERVER_NAME );
		OCIDefineByName($stmt, 'USERNAME'   , $P_USERNAME    );
		OCIDefineByName($stmt, 'PASSWORD'   , $P_PASSWORD    );
		OCIDefineByName($stmt, 'TIMEOUT'    , $P_TIMEOUT     );
		OCIDefineByName($stmt, 'RETRY_CNT'  , $P_RETRY_CNT   );
		OCIDefineByName($stmt, 'STATUS'     , $P_STATUS      );

		//Execute SQL
		OCIExecute($stmt);

		//Execute Query
		$ores     = OCIExecute($stmt);
		$is_error = false;
		$err_msg  = "SUCCESS";
		//error ?
		if(! $ores)
		{
			//Store error	
			$error    = OCIError ($stmt);
			$err_msg  = "Error: ${error['code']} ${error['message']}";
			$is_error = true;
		}

		//Loop thru all the records selected and display it in the table
		$alldata = null;
		if(OCIFetch($stmt))
		{
			$totalrows++;
			//bind
                        $P_ID         ;
                        $P_SERVER_IP  ;
                        $P_SERVER_PORT;
                        $P_SERVER_NAME;
                        $P_USERNAME   ;
                        $P_PASSWORD   ;
                        $P_TIMEOUT    ;
                        $P_RETRY_CNT  ;
                        $P_STATUS     ;
		}

		//Free Oracle connection
		OCIFreeStatement($stmt);
		OCICommit($conn);

		//Log-off connection		
		OCILogoff($conn);

		//event
		//$gEventLogger->LogEvent(__CLASS__, "Get() : [ $sql // $totalrows // $is_error / $err_msg] ");

		$alldata = array(
				'error'		=> $is_error, 
				'error_msg' 	=> $err_msg,
				'bind_total'	=> $totalrows,
				'bind_data'	=> array(
				'id'            => $P_ID          ,
				'server_ip'     => $P_SERVER_IP   ,
				'server_port'   => $P_SERVER_PORT ,
				'server_name'   => $P_SERVER_NAME ,
				'username'      => $P_USERNAME    ,
				'password'      => $P_PASSWORD    ,
				'timeout'       => $P_TIMEOUT     ,
				'retry_cnt'     => $P_RETRY_CNT   ,
				'status'        => $P_STATUS      ,
				));

		return $alldata;

	}

	//chk fields
	function CheckFields($data=null,$ok=0)
	{
		global $gEventLogger;

		//default
		$err          = null;
		$err['error'] = false;
		$err['msg']   = null;


		//user 
		$ret = $this->IfExists($data['server_name'], $data['id'], $ok);
		if($ret > 0)
		{
			$err['error'] = true;
			$err['msg']   = INVALID_RECORD_ALREADY_EXISTS;
			return $err;
		}

		//usr
		if(strlen(trim($data['server_name'])) <= 0)
		{
			$err['error'] = true;
			$err['msg']   = INVALID_HLR_SERVERS;
			return $err;
		}

		return $err;

	}


	//save
	function Save($data=null)
	{
		//globals here
		global $gEventLogger;

		//db
		$myOraDb =& myOraDb::GetInstance(DB_USER,DB_PASS,DB_NAME);

		//format the insert-parameters
		$K     = null;
		$V     = null;
		$kbuff = null;
		$vbuff = null;
		$mbuff = "";
		while( list($idx,$col) = each($data) )
		{
			$K[] = $col['name'];
			if($col['data_type'] == 'string')
				$V[] = "'".ora_addslashes(trim($col['value'])) . "'";
			else
				$V[] = intval(trim($col['value'])) ;

			if (strlen($col['value'])>0)
				$mbuff .= $col['name']."=".$col['value']." ";
		}
		//created-by
		$K[]       = 'created_by';
		$V[]       = "'".ora_addslashes($_SESSION["session.smartygold.profile.user"]) . "'";
		$kbuff     = join(",", $K);
		$vbuff     = join(",", $V);


		//new profile
		$sql       = " INSERT INTO HLR_SERVERS ($kbuff, DT_CREATED )  VALUES ($vbuff, sysdate )";

		//exect
		$data      = $myOraDb->xQuery($sql);

		//ret
		$is_ok     = $data['error'] ? false : true;
		$err_msg   = $data['error_msg'];

		$this->debug("Save() : $sql // $is_ok // $err_msg");

		//event
		//$gEventLogger->LogEvent(__CLASS__, "Save() [ res=>'$is_ok' // sql=$sql // msg=>'$err_msg'] ");
		$gEventLogger->LogEvent(__CLASS__, "NEW HLR: $mbuff");

		return $data;
	}




	//update
	function Update($data=null)
	{
		//globals here
		global $gEventLogger;

		//db
		$myOraDb =& myOraDb::GetInstance(DB_USER,DB_PASS,DB_NAME);

		//format the insert-parameters
		$K     = null;
		$kbuff = null;
		$mbuff = "";
		while( list($idx,$col) = each($data) )
		{
			$this->debug("Update() : $col[name] ===> $col[value]" );
			if($col['name'] == 'id') 
			{
				$id    = intval(trim($col['value']));
				continue;
			}
			if($col['data_type'] == 'string')
				$K[] = $col['name']. " = '".ora_addslashes(trim($col['value'])) . "'";
			else
				$K[] = $col['name']. " = ". intval(trim($col['value']));

			if (strlen($col['value'])>0)
				$mbuff .= $col['name']."=".$col['value']." ";
		}

		//created-by
		$K[] = " updated_by = '".ora_addslashes($_SESSION["session.smartygold.profile.user"])."' ";

		$kbuff     = join(" , ", $K);

		//new profile
		$sql       = " UPDATE HLR_SERVERS SET DT_UPDATED=Sysdate, $kbuff WHERE ID=$id ";

		//exect
		$data      = $myOraDb->xQuery($sql);

		//ret
		$is_ok     = $data['error'] ? false : true;
		$err_msg   = $data['error_msg'];

		$this->debug("Update() : $sql // $is_ok // $err_msg");

		//event
		//$gEventLogger->LogEvent(__CLASS__, "Update() [ res=>'$is_ok' // sql=$sql // errmsg=>'$err_msg'] ");
		$gEventLogger->LogEvent(__CLASS__, "UPDATE HLR: $mbuff");

		return $data;
	}



	//delete
	function Delete($id=0)
	{
		//globals here
		global $gEventLogger;

		//db
		$myOraDb =& myOraDb::GetInstance(DB_USER,DB_PASS,DB_NAME);

		$id        = intval($id);
		//update profile
		$sql       = " DELETE FROM HLR_SERVERS WHERE ID=$id ";

		//exect
		$data      = $myOraDb->xQuery($sql);

		//ret
		$is_ok     = $data['error'] ? false : true;
		$err_msg   = $data['error_msg'];

		$this->debug("Delete() : $sql // $is_ok // $err_msg");

		//event
		//$gEventLogger->LogEvent(__CLASS__, "Delete() [ res=>'$is_ok' // sql=$sql // errmsg=>'$err_msg'] ");
		$gEventLogger->LogEvent(__CLASS__, "DELETE HLR: ID=$id ");

		return $data;
	}


	//get uniq
	function IfExists($c1='',$id=0,$ok=0)
	{
		//globals here
		global $gEventLogger;

		//db
		$myOraDb =& myOraDb::GetInstance(DB_USER,DB_PASS,DB_NAME);
		$conn    = $myOraDb->dbConnection();

		//format the insert-parameters
		$c1        = ora_addslashes(trim($c1));
		$c2        = ora_addslashes(trim($c2));
		$id        = intval(trim($id));

		//update profile
		$sql       = ($id>0 and $ok) ? 
			" SELECT  COUNT(1) as TOTAL FROM HLR_SERVERS WHERE SERVER_NAME='$c1' AND ID!=$id " :
			" SELECT  COUNT(1) as TOTAL FROM HLR_SERVERS WHERE SERVER_NAME='$c1' ";


		
		//Parse SQL passed
		$stmt = OCIParse($conn,$sql);

		//reset	
		$totalrows = 0;	

		//bind
		OCIDefineByName($stmt,"TOTAL",$totalrows);		

		$this->debug("getList() : get_max_row[$sql / $stmt / $totalrows]");

		//Execute Query
		OCIExecute($stmt);

		//chk
		$totalrows = (OCIFetch($stmt)) ? ($totalrows) : (0);

		//Free Query Handle
		OCIFreeStatement($stmt);
		OCICommit($conn);

		// logoff connection
		OCILogoff($conn);

		$this->debug("IfExists() : // $sql // $totalrows");

		//event
		//$gEventLogger->LogEvent(__CLASS__, "IfExists() [ $sql // $totalrows ] ");

		return $totalrows;


	}


	//get form fields
	function getTableFields()
	{
		global $_POST;
		global $gSelectOptionsMaster;
		$opts_status = $gSelectOptionsMaster->getStatus();

		$table_fields= array(
				array('name' => 'server_name'  ,'title'=>'Server Name',   'type' => 'text',    'max'=> 60,   'data_type'=> 'string', 'value' => $_POST['server_name'] ),
				array('name' => 'server_ip'    ,'title'=>'IP Address',    'type' => 'text',    'max'=> 30,   'data_type'=> 'string', 'value' => $_POST['server_ip'  ] ),
				array('name' => 'server_port'  ,'title'=>'Port',          'type' => 'text',    'max'=> 16,   'data_type'=> 'string', 'value' => $_POST['server_port'] ),
				array('name' => 'username'     ,'title'=>'Username',      'type' => 'text',    'max'=> 60,   'data_type'=> 'string', 'value' => $_POST['username'   ] ),
				array('name' => 'password'     ,'title'=>'Password',      'type' => 'text',    'max'=> 60,   'data_type'=> 'string', 'value' => $_POST['password'   ] ),
				array('name' => 'timeout'      ,'title'=>'Timeout (ms)',  'type' => 'text',    'max'=> 12,   'data_type'=> 'string', 'value' => $_POST['timeout'    ] ),
				array('name' => 'retry_cnt'    ,'title'=>'Retry',         'type' => 'text',    'max'=> 12,   'data_type'=> 'string', 'value' => $_POST['retry_cnt'  ] ),
				array('name' => 'status'       ,'title'=>'Status',        'type' => 'select',  'max'=> 12,   'data_type'=> 'string', 'value' => $_POST['status'     ], 'select_options'=>$opts_status, 'select_selected' => $_POST['status'] ),
				);

		return $table_fields;

	}


	//get form fields
	function getTableFields4Update($id="")
	{
		global $gSelectOptionsMaster;

		$data        = $this->Get($id);			
		$post        = $data['bind_data'];
		$opts_status = $gSelectOptionsMaster->getStatus();

		$table_fields= array(
				array('name' => 'server_name'  ,'title'=>'Server Name',   'type' => 'text',    'max'=> 60,   'data_type'=> 'string', 'value' => $post['server_name'] ),
				array('name' => 'server_ip'    ,'title'=>'IP Address',    'type' => 'text',    'max'=> 30,   'data_type'=> 'string', 'value' => $post['server_ip'  ] ),
				array('name' => 'server_port'  ,'title'=>'Port',          'type' => 'text',    'max'=> 16,   'data_type'=> 'string', 'value' => $post['server_port'] ),
				array('name' => 'username'     ,'title'=>'Username',      'type' => 'text',    'max'=> 60,   'data_type'=> 'string', 'value' => $post['username'   ] ),
				array('name' => 'password'     ,'title'=>'Password',      'type' => 'text',    'max'=> 60,   'data_type'=> 'string', 'value' => $post['password'   ] ),
				array('name' => 'timeout'      ,'title'=>'Timeout (ms)',  'type' => 'text',    'max'=> 12,   'data_type'=> 'string', 'value' => $post['timeout'    ] ),
				array('name' => 'retry_cnt'    ,'title'=>'Retry',         'type' => 'text',    'max'=> 12,   'data_type'=> 'string', 'value' => $post['retry_cnt'  ] ),
				array('name' => 'status'       ,'title'=>'Status',        'type' => 'select',  'max'=> 12,   'data_type'=> 'string', 'value' => $post['status'     ], 'select_options'=>$opts_status, 'select_selected' => $post['status'] ),
				);

		return $table_fields;

	}

	//list
	function getList($extravars=null)
	{

		//globals here
		global $gEventLogger;

		//db
		$myOraDb =& myOraDb::GetInstance(DB_USER,DB_PASS,DB_NAME);


		//reset
		$retdata               = null;
		$retdata['total']      = 0;
		$retdata['data']       = null;
		$retdata['links']      = 'No result found';


		$filters=0;
		$xwherelog = "";
		$temp_code      = $_GET['scode'];
		$temp_desc      = $_GET['sname'];
		$temp_stat      = $_GET['sstat'];
		$temp_file  = $_GET['sfile'];
		$temp_sort  = $_GET['sortedby'];

		if (strlen($temp_code) > 0) { 
			$xwhere .= " AND server_ip like '$temp_code%' "; 
			$xwherelog .= " Server IP=$temp_code";
			$filters++; 
		}

		if (strlen($temp_desc) > 0) { 
			$xwhere .= " AND server_name like '$temp_desc%' "; 
			$xwherelog .= " Server Name=$temp_desc";
			$filters++; 
		}

		if ((strlen($temp_stat) > 0)  && ($temp_stat != 'ALL')) { 
			$xwhere .= " AND status like '$temp_stat%' "; 
			$xwherelog .= " Status=$temp_stat";
			$filters++; 
		}

		if ($filters > 0) {
			$xwhere = " where 1 =1 ". $xwhere;
		}
		else {
			$xwherelog .= " All";
		}
	
		//fmt
		$whatsort    = $extravars['whatsort'];
		//type of order
		$ascdesc     = (!$whatsort) ? (" ASC ") : ( " DESC " );	

		// logon to oracle database
		$conn = $myOraDb->dbConnection();

		//select values for dynamic SQL passing
		$sql  = " SELECT COUNT(1) AS COUNT FROM HLR_SERVERS $xwhere";

		//Parse SQL passed
		$stmt = OCIParse($conn,$sql);

		//reset	
		$totalrows = 0;	

		//bind
		OCIDefineByName($stmt,"COUNT",$totalrows);		

		$this->debug("getList() : get_max_row[$sql / $stmt / $totalrows]");

		//Execute Query
		OCIExecute($stmt);

		//chk
		$totalrows = (OCIFetch($stmt)) ? ($totalrows) : (0);

		//Free Query Handle
		OCIFreeStatement($stmt);

		// logoff connection
		OCILogoff($conn);

		//event
		//$gEventLogger->LogEvent(__CLASS__, "getHlrSrvrList : [WHERE=>'$xwhere'] [ TOTAL=>'$totalrows'] ");

		//chk rowcount
		if(0 == $totalrows )     
		{
                	$retdata['scode']     = $temp_code ;
                	$retdata['sname']     = $temp_desc ;
                	$retdata['sstat']     = $temp_stat ;
			return $retdata;	
		}                   

		// prepare download file
		if ((strlen($temp_file)>0) && ($temp_file==1)) {
			$csvdtl = $this->downloadList($extravars=null);
			$retdata['csv'] = $csvdtl['csvfilename'];
		}
		else {
			//-------------------------------
			//  set up pager
			//-------------------------------
                	
			$offset        = SEARCH_MAX_OFFSET_MORE;
			$pager_options = array(
					'totalItems'   => $totalrows,
					'perPage'      => $offset,
					'delta'        => 2,             // for 'Jumping'-style a lower number is better
					'append'       => true,
					'separator'    => '&nbsp;',
					'clearIfVoid'  => false,
					'urlVar'       => 'nextpg',
					'extraVars'    => $extravars,
					'useSessions'  => true,
					'closeSession' => true,
					'httpMethod'   => 'GET',
					'mode'         => 'Sliding',    //try switching modes
					//'mode'  => 'Jumping',
					);
                	
			$pager = Pager::factory($pager_options);
			$page = array();
			$page['totalItems']   = $pager_options['totalItems'];
			$page['links']        = $pager->links;
			$page['page_numbers'] = array(
					'current' => $pager->getCurrentPageID(),
					'total'   => $pager->numPages()
					);
			//paging here
			list($page['from'], $page['to']) = $pager->getOffsetByPageId();
			$slimit    = intval($page['from']);
			$elimit    = $slimit + intval($pager_options['perPage']);    
                	
			//event
			$nPageNo=$pager->getCurrentPageID();
			$nPageTo=$pager->numPages();
			$gEventLogger->LogEvent(__CLASS__, "SEARCHED By: $xwherelog TOTAL Rows=$totalrows PAGE=$nPageNo/$nPageTo");
                	
                	
			//logon to oracle database
			$conn = $myOraDb->dbConnection();
                	
			//orderby
			//$sortedby       = $extravars['sortedby'];
			$sortedby       = $temp_sort;
			switch($sortedby)
			{
				case 'server_ip':
					$orderby = " ORDER BY  server_ip $ascdesc";
					break;
				case 'server_name':
					$orderby = " ORDER BY  server_name $ascdesc";
					break;
				case 'status':
					$orderby = " ORDER BY  status $ascdesc";
					break;
				default:	
					$orderby = " ORDER BY id $ascdesc";
					break;
			}
                	
			//select values for dynamic SQL passing
			$sql  = "  SELECT * FROM ( SELECT ROWNUM MROW, b.* FROM ( SELECT id, server_ip, server_port, server_name, username, password, timeout, retry_cnt, status FROM HLR_SERVERS ".
				"  $xwhere $orderby ) b ) WHERE MROW >=$slimit AND MROW < $elimit";
                	
			//Parse SQL passed
			$stmt = OCIParse($conn,$sql);
                	
			$this->debug("getHlrSrvrList() : get_all_rows[$sql / $stmt / $totalrows]");
                	
                	
			//bind
			OCIDefineByName($stmt, 'ID'         , $p_id          );
			OCIDefineByName($stmt, 'SERVER_IP'  , $p_server_ip   );
			OCIDefineByName($stmt, 'SERVER_PORT', $p_server_port );
			OCIDefineByName($stmt, 'SERVER_NAME', $p_server_name );
			OCIDefineByName($stmt, 'USERNAME'   , $p_username    );
			OCIDefineByName($stmt, 'PASSWORD'   , $p_password    );
			OCIDefineByName($stmt, 'TIMEOUT'    , $p_timeout     );
			OCIDefineByName($stmt, 'RETRY_CNT'  , $p_retry_cnt   );
			OCIDefineByName($stmt, 'STATUS'     , $p_status      );
                	
                	
			//Execute SQL
			OCIExecute($stmt);
                	
			//Loop thru all the records selected and display it in the table
			$idx = 0;
			while (OCIFetch($stmt))
			{
				$alldata[$idx]['id'         ]    = $p_id          ;
				$alldata[$idx]['server_ip'  ]    = $p_server_ip   ;
				$alldata[$idx]['server_port']    = $p_server_port ;
				$alldata[$idx]['server_name']    = $p_server_name ;
				$alldata[$idx]['username'   ]    = $p_username    ;
				$alldata[$idx]['password'   ]    = $p_password    ;
				$alldata[$idx]['timeout'    ]    = $p_timeout     ;
				$alldata[$idx]['retry_cnt'  ]    = $p_retry_cnt   ;
				$alldata[$idx]['status'     ]    = $p_status      ;
				$idx++;
			}
                	
                	
                	
			//Free Oracle connection
			OCIFreeStatement($stmt);
                	
			//Log-off connection		
			OCILogoff($conn);
                	
			//save
			$retdata['data']      = $alldata;
		}

		$retdata['total']     = $totalrows ;
                $retdata['scode']     = $temp_code ;
                $retdata['sname']     = $temp_desc ;
                $retdata['sstat']     = $temp_stat ;

		//no rec
		if($totalrows <=0 )
		{
			$retdata['links'] = 'No result found';
		}
		else
		{
			$navigator            = $page['links'];
			$retdata['links']     = $navigator;
		}				 

		//return data
		return $retdata;
	}

	//list
	function downloadList($extravars=null)
	{

		//globals here
		global $gEventLogger;

		//db
		$myOraDb =& myOraDb::GetInstance(DB_USER,DB_PASS,DB_NAME);

		//reset
		$retdata               = null;

		$filters=0;
		$temp_code      = $_GET['scode'];
		$temp_desc      = $_GET['sname'];
		$temp_stat      = $_GET['sstat'];

		if (strlen($temp_code) > 0) { 
			$xwhere .= " AND server_ip like '$temp_code%' "; 
			$filters++; 
		}

		if (strlen($temp_desc) > 0) { 
			$xwhere .= " AND server_name like '$temp_desc%' "; 
			$filters++; 
		}

		if ((strlen($temp_stat) > 0)  && ($temp_stat != 'ALL')) { 
			$xwhere .= " AND status like '$temp_stat%' "; 
			$filters++; 
		}

		if ($filters > 0) {
			$xwhere = " where 1 =1 ". $xwhere;
		}

		//logon to oracle database
		$conn = $myOraDb->dbConnection();

		//orderby
		$sortedby  = $extravars['sortedby'];
		$ascdesc   = " ASC ";	
		$orderby   = " ORDER BY  server_ip $ascdesc";

		//select values for dynamic SQL passing
		$sql  = "  SELECT * FROM ( SELECT ROWNUM MROW, b.* FROM ( SELECT id, server_ip, server_port, server_name, username, password, timeout, retry_cnt, status, created_by, ".
			"   to_char(dt_created, 'YYYY-MM-DD HH24:MI:SS') dt_created, updated_by modified_by, to_char(dt_updated, 'YYYY-MM-DD HH24:MI:SS') dt_modified ". 
			"   FROM HLR_SERVERS ".
			"  $xwhere $orderby ) b ) ";

		//Parse SQL passed
		$stmt = OCIParse($conn,$sql);

		$this->debug("downloadList() : get_all_rows[$sql / $stmt / $totalrows]");

		//bind
		OCIDefineByName($stmt, 'ID'         , $p_id           );
		OCIDefineByName($stmt, 'SERVER_IP'  , $p_server_ip    );
		OCIDefineByName($stmt, 'SERVER_PORT', $p_server_port  );
		OCIDefineByName($stmt, 'SERVER_NAME', $p_server_name  );
		OCIDefineByName($stmt, 'USERNAME'   , $p_username     );
		OCIDefineByName($stmt, 'PASSWORD'   , $p_password     );
		OCIDefineByName($stmt, 'TIMEOUT'    , $p_timeout      );
		OCIDefineByName($stmt, 'RETRY_CNT'  , $p_retry_cnt    );
		OCIDefineByName($stmt, 'STATUS'     , $p_status       );
		OCIDefineByName($stmt, 'CREATED_BY' , $p_created_by   );
		OCIDefineByName($stmt, 'DT_CREATED' , $p_dt_created   );
		OCIDefineByName($stmt, 'MODIFIED_BY', $p_modified_by  );
		OCIDefineByName($stmt, 'DT_MODIFIED', $p_dt_modified  );

		//Execute SQL
		OCIExecute($stmt);

		//open csv here
		$csvfnam = date("Ymd").'.'.get_uid().".csv";
		$csvfile = SMARTY_INC_DIR."/log/$csvfnam";
		$csvcont = '';

		//Loop thru all the records selected and display it in the table
		$idx = 0;
		while (OCIFetch($stmt))
		{
			//csv-create
			$csvdata   = null;
			$csvdata[] = $p_server_ip    ; 
			$csvdata[] = $p_server_port  ; 
			$csvdata[] = $p_server_name  ; 
			$csvdata[] = $p_username     ; 
			$csvdata[] = $p_password     ; 
			$csvdata[] = $p_timeout      ; 
			$csvdata[] = $p_retry_cnt    ; 
			$csvdata[] = $p_status       ; 
			$csvdata[] = $p_created_by   ; 
			$csvdata[] = $p_dt_created   ; 
			$csvdata[] = $p_modified_by  ;
			$csvdata[] = $p_dt_modified  ;
			$csvstr    = join('","', $csvdata);
			$csvcont .= "\"$csvstr\"\n";
			$idx++;
		}

		//close csv
                $rep_gen_dt = date("Y-m-d H:i:s");
                file_save($csvfile,"List of HLR Servers\nReport Generation: $rep_gen_dt\n\n".CSV_HEADER_HLRSVR_REPORT."\n\n".$csvcont);

		//Free Oracle connection
		OCIFreeStatement($stmt);

		//Log-off connection		
		OCILogoff($conn);

		//event
		$retdata['csvfilename'] = $csvfnam;

		//return data
		return $retdata;
	}

	//1-only
	function &GetInstance()
	{
		static $instance;

		if(empty($instance) or !$instance or !defined($instance))
		{
			$instance   = new HlrSrvr();
		}

		return $instance;
	}

	//for debugging
	function debug($msg='')
	{
		global $gLoggerConf,$gDebug,$gLogDebug;

		$tm   = date("Y-m-d H:i:s");
		$buff = sprintf("DEBUG: [%15s] : %s : %s", " ----- " , $tm, $msg); 

		if($gDebug)
		{
			echo "$buff<hr>\n";	

		}
		if($gLogDebug)
		{
			$logger = &Log::singleton('file', WEBLOG, __CLASS__, $gLoggerConf);
			$logger->log($msg);
		}
	}

}//class	
?>
