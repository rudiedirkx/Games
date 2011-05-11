<?php

class db_mysql
{

	public $m_hResult;
	public $m_szQuery;
	public $errstr;
	public $errno;

	public $m_iNumQueries		= 0;
	public $m_arrHistory		= Array();

	public $m_arrSpecialIns	= Array(
		"NOW()",
		"NULL",
	);

	public $m_szTable;


	/**
	 * @brief	CONSTRUCTOR
	 */
    public function __construct( $f_szDb = SQL_DB, $f_szUser = SQL_USER, $f_szPass = SQL_PASS )
	{
		// connect to the server
		$f_szHost = 'localhost';
		$this->m_hSQLCon = mysql_connect( $f_szHost, $f_szUser, $f_szPass ) or $this->Error('Connect fails!');

		// select the database
		mysql_select_db( $f_szDb, $this->m_hSQLCon ) or $this->Error('Db selection fails!');

		// set default character set
//		$this->query("SET CHARACTER SET 'latin1';");

	} # END __construct() */


	/**
	 * @brief	Error
	 */
	public function Error( $f_szErrstr = NULL, $f_iErrno = NULL )
	{
		$this->errno	= is_null($f_iErrno)	? mysql_errno() : $f_iErrno;
		$this->errstr	= is_null($f_szErrstr)	? mysql_error() : $f_szErrstr;

		throw new Exception( (string)$this->errstr, (int)$this->errno );

	} # END Error() */


	/**
	 * @brief	query
	 */
	public function query( $f_szQuery )
	{
		$this->m_iNumQueries++;
		$this->m_arrHistory[] = $f_szQuery;

		$this->errno	= 0;
		$this->errstr	= "";

		// save query
		$this->m_szQuery = $f_szQuery;

		// run the query
		$r = mysql_query( $f_szQuery, $this->m_hSQLCon );
		if ( $r )
		{
			$this->m_hResult = $r;
			$copy = clone $this;
			return $copy;
		}

		$this->Error();

		return false;

	} # END query() */


	/**
	 * @brief	numRows
	 */
	public function numRows()
	{
		if ( empty($this->m_hResult) OR !is_resource($this->m_hResult) ) return FALSE;

		return mysql_num_rows($this->m_hResult);

	} /**/// END numRows( )


	/**
	 * @brief	affectedRows
	 */
	public function affectedRows()
	{
		return mysql_affected_rows($this->m_hSQLCon);

	} /**/// END affectedRows( )


	/**
	 * @brief	countRows
	 */
	public function countRows( $f_szTable, $f_szWhereClause = '1' )
	{
		return $this->select($f_szTable, "COUNT(1)", $f_szWhereClause)->fetchField();

	} /**/// END countRows( )


	/**
	 * @brief	fetchAssoc
	 */
	public function fetchAssoc()
	{
		if ( empty($this->m_hResult) || !is_resource($this->m_hResult) ) return FALSE;

		return mysql_fetch_assoc($this->m_hResult);

	} /**/// END fetchAssoc( )


	/**
	 * @brief	fetchAll
	 * 
	 */
	public function fetchAll()
	{
		if ( empty($this->m_hResult) || !is_resource($this->m_hResult) ) return FALSE;

		$arrResults = array();
		while ( $r = $this->fetchAssoc() )
		{
			$arrResults[] = $r;
		}

		return $arrResults;

	} # END fetchAll() */


	/**
	 * @brief	fetchRow
	 */
	public function fetchRow()
	{
		if ( empty($this->m_hResult) || !is_resource($this->m_hResult) ) return FALSE;

		$arrNumeric = mysql_fetch_row($this->m_hResult);
		return $arrNumeric;

	} /**/// END fetchRow( )


	/**
	 * @brief	fetchField
	 */
	public function fetchField( $f_iField = 0, $f_mixRow = 0 )
	{
		if ( empty($this->m_hResult) OR !is_resource($this->m_hResult) ) return FALSE;

		$scalarVal = mysql_result($this->m_hResult, $f_mixRow, $f_iField);
		return $scalarVal;

	} /**/// END fetchField( )


	/**
	 * @brief	insert
	 */
	public function insert( $f_szTable, $f_arrValues = Array() )
	{
		if ( !is_array($f_arrValues) || !count($f_arrValues) ) return;

		$arrFields = array_keys($f_arrValues);

		$arrValues = Array();
		while ( list(,$szValue) = each($f_arrValues) )
		{
			if ( in_array(strtoupper($szValue), $this->m_arrSpecialIns) )
			{
				$arrValues[] = $szValue;
			}
			else
			{
				$arrValues[] = "'" . ($szValue) . "'";
			}
		}

		$szQuery = "INSERT INTO `" . $f_szTable . "` (`" . implode( "`, `", $arrFields ) . "`) VALUES (" . implode( ",", $arrValues ) . ");";
// echo $szQuery; return;

		return $this->query($szQuery);

	} /**/// END insert( )


	/**
	 * @brief	update
	 */
	public function update( $f_szTable, $f_arrValues, $f_szWhereStatement = "1" )
	{
		if ( !is_array($f_arrValues) || !count($f_arrValues) ) return true;

		$arrValues = array();
		foreach ( $f_arrValues AS $szField => $szValue )
		{
			if ( in_array(strtoupper($szValue), $this->m_arrSpecialIns) )
			{
				$arrValues[] = "`" . $szField . '` = ' . ($szValue);
			}
			else
			{
				$arrValues[] = "`" . $szField . "` = '" . ($szValue) . "'";
			}
		}

		$szQuery = "UPDATE `" . $f_szTable . "` SET " . implode( ", ", $arrValues ) . " WHERE ".$f_szWhereStatement.";";

		return $this->query($szQuery);

	} /**/// END update( )


	/**
	 * @brief	select
	 */
	public function select( $f_szTable, $f_szWhereClause = "", $f_szExtra = "" )
	{
		$szWhereClause = $f_szWhereClause ? " WHERE " . $f_szWhereClause : "";
		$szQuery = "SELECT * FROM " . $f_szTable . $szWhereClause . " " . $f_szExtra . ";";

		return $this->fetchExAll($szQuery);

	} # END select() */


	/**
	 * @brief	fetchExAll
	 * 
	 */
	public function fetchExAll( $f_szQuery )
	{
		$r = $this->query($f_szQuery);
		return $r->fetchAll();

	} # END fetchExAll() */


	/**
	 * @brief	delete
	 */
	public function delete( $f_szTable, $f_szWhereClause = '1' )
	{
		$szQuery = "DELETE FROM `" . $f_szTable . "` WHERE " . $f_szWhereClause . ";";

		return $this->query($szQuery);

	} /**/// END delete( )


	/**
	 * @brief	where
	 */
	public function where( $szParams )
	{
		$arrParams = explode (",", $szParams);
		if ( count($arrParams)%2 ) $szParams[] = "";
		$arrParts = Array();
		for ( $i=0; $i<count($arrParams); $i+=2 )
		{
			$arrParts[$arrParams[$i]] = $arrParams[$i+1];
		}

		$arrConditions = Array();
		foreach ( $arrParts AS $szField => $szConditions )
		{
			$szField		= trim($szField);
			$szConditions	= trim($szConditions);
	
			if ( strstr($szConditions, "-") )
			{
				// 3 possibilities:
				if ( "-" == substr($szConditions, 0, 1) )
				{
					// less then
					$lessthen = (int)ltrim(substr($szConditions, 1), "- ");
					$arrConditions[] = $szField . " < " . $lessthen;
				}
				else if ( "-" == substr($szConditions, -1, 1) )
				{
					// greater then
					$greaterthen = (int)rtrim(substr($szConditions, 0, -1), "- ");
					$arrConditions[] = $szField . " > " . $greaterthen;
				}
				else
				{
					// between
					list($from, $to) = explode("-", $szConditions, 2);
					$arrConditions[] = $szField . " BETWEEN " . (float)$from . " AND " . (float)$to . "";
				}
			}
			else
			{
				$x = explode("|", $szConditions);
				$arrValues = Array();
				foreach ( $x AS $val )
				{
					if ( strlen(trim($val)) )
					{
						$arrValues[] = "'" . $val . "'";
					}
				}

				$arrConditions[] = "" . $szField . " IN (" . implode(",", $arrValues) . ")";
			}
		}

		return implode(" AND ", $arrConditions);

	} // END where( )


} // END Class Database_MySQL

?>