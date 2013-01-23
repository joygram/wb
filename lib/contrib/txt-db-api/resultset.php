<?php
/**********************************************************************
						 Php Textfile DB API
						Copyright 2003 by c-worker.ch
						  http://www.c-worker.ch
***********************************************************************/
/**********************************************************************
Redistribution and use in source and binary forms, with or without 
modification, are permitted provided that the following conditions are met: 
Redistributions of source code must retain the above copyright notice, this 
list of conditions and the following disclaimer. 
Redistributions in binary form must reproduce the above copyright notice, 
this list of conditions and the following disclaimer in the documentation 
and/or other materials provided with the distribution. 
THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE 
IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE 
ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE 
LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR 
CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF 
SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS 
INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN 
CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF 
THE POSSIBILITY OF SUCH DAMAGE. 
***********************************************************************/

include_once(API_HOME_DIR . "const.php");
include_once(API_HOME_DIR . "util.php");


/**********************************************************************
								Row
***********************************************************************/

// ResultSet->rows should be of this type (not really used at the moment)
class Row 
{
	var $id;   			 // unique id for the row
	var $fields=array(); // fields of the row
}

/**********************************************************************
							ResultSet
***********************************************************************/
// Represents a Table
class ResultSet 
{
	/***********************************
		 	Mebmer Variables
	************************************/
	var $debug = 1;
	// columns
	var $colNames=array();
	var $colAliases=array();
	var $colTables=array();
	var $colTableAliases=array();
	var $colTypes=array();
	var $colDefaultValues=array();
	var $colFuncs=array();
	var $colFuncsExecuted=array();

	
	// rows 
	var $rowIds=array() ;
	var $rows=array();  // to use as array of type Row (see above)
	
	// position in the ResultSet
	var $pos=-1;
		
	// informations how this resultSet is ordered 
	// at the momemt only used by cmpRows()
	var $orderColNrs=array(); // Column Nr
	var $orderTypes=array();  // ORDER_ASC or ORDER_DESC
	
	/***********************************
		 	row id functions 
	************************************/
	function setRowId($rowNr, $id) 
	{
		//$this->rows[$rowNr]->id=$id;
		$this->rowIds[$rowNr] = $id ;
	}
	function getRowId($rowNr) 
	{
		//return $this->rows[$rowNr]->id;
		return $this->rowIds[$rowNr] ;
	}
	function setCurrentRowId($id) 
	{
		//$this->rows[$this->pos]->id=$id;
		$this->rowIds[$this->pos] = $id ;
	}
	function getCurrentRowId() 
	{
		//return $this->rows[$this->pos]->id;
		return $this->rowIds[$this->pos] ; 
	}
	function searchRowById($id) 
	{
		/*
		for($i=0;$i<count($this->rowIds);++$i) {
			if(isset($this->rows[$i]->id) && $this->rows[$i]->id==$id)
				return $i;
		*/
		for($i=0;$i<count($this->rowIds); ++$i)
		{
			if(isset($this->rowIds) && $this->rowIds[$i]==$id)
				return $i ;
		}
		return NOT_FOUND;
	}
   
	/***********************************
		 	Navigate Functions
	************************************/
	function getPos() 
	{
		if($this->debug) echo("getPos()<br>") ;
		return $this->pos;
	}
	function setPos($pos) 
	{
		if($this->debug) echo("setPos(), pos[$pos]<br>") ;
		$this->pos=$pos;
	}
	function reset() 
	{
		$this->pos=-1;
	}
	// Moves to the next row and returns true if there was a next row
	// or false if there was no next row
	function next() 
	{
		if($this->debug) echo("next(),count(this->rowIds)[".count($this->rowIds)."]<br>") ;
		$walk = 1 ;
		while( $walk && ++$this->pos < count($this->rowIds) )
		{
			if($this->debug) echo("next(),walking this->pos[{$this->pos}], {$this->rowIds[$this->pos]}<br>") ;

			if($this->rowNrDeleted($this->pos))
			{
				if($this->debug) echo("next(),this->pos[$this->pos],this->rowIds[$this->pos]=[{$this->rowIds[$this->pos]}]<br>") ;
			}
			else
			{
				$walk = 0 ;

			}
		}

		if($this->pos < count($this->rowIds))
		{
			if($this->debug) echo("next(),this->pos[{$this->pos}]<br>") ;
			return true;
		}
		else 
		{
			$this->prev() ;
			if($this->debug) echo("next(),overflow, this->pos[{$this->pos}]<br>") ;
			return false;
		}
	}
	
 	function prev() 
	{
		$walk = 1 ;
		while( $walk && --$this->pos > -1)
		{
			if($this->rowNrDeleted($this->pos)) 
			{
				$walk = 0 ;
			}
		}

		if($this->pos<count($this->rowIds))
		{
			return true;
		}
 		else
			return false;
	}

 	function end() 
	{
		//$this->pos= count($this->rowIds)-1;
		$this->pos = count($this->rowIds)-1; 
 	}
 	function start() 
	{
 		$this->reset();
 	}
 	

	// by lovjesus 
	function insertRowByPos($rowNr, $row)
	{
		//if($this->debug) echo("insertRowByPos(), $rowNr<br>") ;
		$colName_cnt = count($this->colNames) ;
		for($i = 0; $i < $colName_cnt; ++$i)
		{
			//if($this->debug) echo("insertRowByPos(), i=$i<br>") ;
			$this->rows[$this->colNames[$i]][$rowNr] = $row[$i] ;
		}
	}

	// by lovjesus
	function insertRowFromRows($rowNr, &$srcRows, $srcRowNr) 
	{
		if($this->debug) echo("insertRowFromRows(),rowNr[$rowNr],srcRowNr[$srcRowNr]<br>") ;

		for($i = 0; $i < count($this->colNames); ++$i)
		{
			$this->rows[$this->colNames[$i]][$rowNr] = $srcRows[$this->colNames[$i]][$srcRowNr] ;
		}

	}

 	// Appends a row to the ResultSet
 	function append($setDefaultValues=true, $cnt_rowIds = -1) 
	{
		//if($this->debug) echo("append(),setDefaultValues[$setDefaultValues]this->pos[$this->pos]<br>") ;
		if($cnt_rowIds == -1) 
		{
			$cnt_rowIds = count($this->rowIds) ;
		}

 		$this->pos= $cnt_rowIds -1;
		//if($this->debug) echo("append(),before inc this->pos[{$this->pos}]<br>") ;
 		if(++$this->pos!= $cnt_rowIds) 
		{
 			print_error_msg("append() failed, not at the end of the ResultSet");
 			$pos--;
 			return false;
 		}


 		if(!$setDefaultValues)
		{
			//if($this->debug) echo("append(),setDefaultValue[$setDefaultValues] RETURN<br>") ;
 			return;
		}
 		
 		// Set initial values
		//한 열을 추가 할 때 마다 rowIds을 자동으로 추가해야만 한다.
		$this->rowIds[$this->pos] = -1 ;//default id setting 

 		for($i=0;$i<count($this->colTypes);++$i) 
		{
			if($this->debug) echo("append(),this->colNames[$i]=[{$this->colNames[$i]}]<br>");
 			// inc
			//다른 방식으로 구현...
 			if($this->colTypes[$i]==COL_TYPE_INC) 
			{
 				if($this->pos==0) 
				{
 					$this->rows[$this->colNames[$i]][$this->pos]=1;
 				} 
				else 
				{
					$this->rows[$this->colNames[$i]][$this->pos] = $this->rows[$this->colNames[$i]][$this->pos-1]+1;
 				}
 			// int
 			} 
			else if($this->colTypes[$i]==COL_TYPE_INT) 
			{			
 				// make sure the default value is a number
 				//$this->rows[$this->colNames[$i]][$this->pos]=intval($this->colDefaultValues[$i]);
 			// str
 			} 
			else 
			{
 				$this->rows[$this->colNames[$i]][$this->pos]=$this->colDefaultValues[$i];
 			}
 		}

	}
  
  	/***********************************
		 	Column Functions (find)
	************************************/
	//
	function removeColByColNr($colNr)
	{
		$rowCount=count($this->rowIds);
		$colName = $this->colNames[$colNr] ;
		for($i = 0; $i < $rowCount; ++$i)
		{
			unset($this->rows[$colName][$i]) ;
		}
	}

	
	// Find a column by its full name, which is in the format
	// FUNC(table.column). 'FUNC' and 'table.' are voluntary. 
	// Returns the column number or NOT_FOUND if the column was not found
	function findColNrByFullName($fullColName) 
	{
		$colName="";
		$colTable="";
		$colFunc="";	
		split_full_colname($fullColName,$colName,$colTable,$colFunc);
		return $this->findColNrByAttrs($colName,$colTable,$colFunc);
	}
	
	// Find a the number of a column with a SqlQuery object and an index into it
	// Returns the column number or NOT_FOUND if the column was not found
	function findColNrBySqlQuery(&$sqlQuery,$index) 
	{
		$colName=($sqlQuery->colAliases[$index]?$sqlQuery->colAliases[$index]:$sqlQuery->colNames[$index]);
		return $this->findColNrByAttrs($colName,$sqlQuery->colTables[$index],$sqlQuery->colFuncs[$index]);
	}
		
	// Find a column by its attributes (name, table, function)
	// Returns the column number or NOT_FOUND if the column was not found
	function findColNrByAttrs($colName, $colTable, $colFunc) 
	{
		$colFunc=strtoupper($colFunc);
		debug_print("Searching for Column: $colName, $colTable, $colFunc...&nbsp;&nbsp;&nbsp;&nbsp;");
		
		// search for colName in the alias first
		if($colName) {
			for($i=0;$i<count($this->colAliases);++$i) 
			{
				// a column can be matched per alias, but only is the $colFunc param is ""
				// or the column functions are the same
				if($colName==$this->colAliases[$i] && 
				  (!$colFunc || $colFunc==$this->colFuncs[$i])) {
					debug_print("found at pos $i<br>");
					return $i;
				}
			}
		}
		
		// colName and colTable params are set
		if($colName && $colTable) 
		{
			for($i=0;$i<count($this->colNames);++$i) 
			{
				if($colName==$this->colNames[$i] && 
				  ($colTable==$this->colTables[$i] || $colTable==$this->colTableAliases[$i]) && 
				  $colFunc==$this->colFuncs[$i]) {
				  	debug_print("found at pos $i<br>");
					return $i;
				}
			}
			debug_print("NOT found!<br>");
			return NOT_FOUND;
		}
		
		// only with colName param is set
		if($colName) 
		{
			for($i=0;$i<count($this->colNames);++$i) 
			{
				if($colName==$this->colNames[$i] &&  $colFunc==$this->colFuncs[$i]) 
				{
					debug_print("found at pos $i<br>");
					return $i;
				}
			}
			debug_print("NOT found!<br>");
			return NOT_FOUND;
		}
		
		// only colFunc param is set
		if($colFunc) 
		{
			for($i=0;$i<count($this->colFuncs);++$i) 
			{
				if($colFunc==$this->colFuncs[$i] && (!$this->colNames[$i]) && (!$this->colAliases[$i])) 
				{
					debug_print("found at pos $i<br>");
					return $i;
				}
			}
			debug_print("NOT found!<br>");
			return NOT_FOUND;
		}
		debug_print("NOT found!<br>");
		return NOT_FOUND;
	}
	
	
	/***********************************
		 Column Functions (set/get)
	************************************/
	// names
	function getColumnNames() 
	{
		return $this->colNames;
	}
	function setColumnNames($colNames) 
	{
		$this->colNames=$colNames;
	}
	
	// aliases
	function getColumnAliases() 
	{
		return $this->colAliases;
	}
	function setColumnAliases($colAliases) 
	{
		$this->colAliases=$colAliases;
	}
	function setColumnAlias($colNr, $colAlias) 
	{
		$this->colAliases[$colNr]=$colAlias;
	}
	
	// tables
	function getColumnTables() 
	{
		return $this->colTables;
	}
	function setColumnTables($colTables) 
	{
		$this->colTables=$colTables;
	}
	function setColumnTableForAll($colTable) 
	{
		$this->colTables=create_array_fill(count($this->colNames),$colTable);
	}
	
	// table aliases
	function getColumnTableAliases() 
	{
		return $this->colTableAliases;
	}
	function setColumnTableAliases($colTableAliases) 
	{
		$this->colTableAliases=$colTableAliases;
	}
	function setColumnTableAliasForAll($colTableAlias) 
	{
		$this->colTableAliases=create_array_fill(count($this->colNames),$colTableAlias);
	}
	
	// types	
	function getColumnTypes() 
	{
		return $this->colTypes;
	}
	function setColumnTypes($colTypes) 
	{
		$this->colTypes=$colTypes;
	}
	
	// default values
	function getColumnDefaultValues() 
	{
		return $this->colDefaultValues;
	}
	function setColumnDefaultValues($colDefaultValues) 
	{
		$this->colDefaultValues=$colDefaultValues;
	}
	
	// functions
	function getColumnFunctions() 
	{
		return $this->colFuncs;
	}
	function setColumnFunctions($colFuncs) 
	{
		$this->colFuncs=$colFuncs;
	}
	function setColumnFunction($colNr, $colFunc) 
	{
		$this->colFuncs[$colNr]=$colFunc;
	}

	/***********************************
		 Column Functions (other)
	************************************/
	// copies all column data from another ResultSet
	function copyColumData($otherResultSet) 
	{
		$this->setColumnNames($otherResultSet->getColumnNames());
		$this->setColumnAliases($otherResultSet->getColumnAliases());
		$this->setColumnTables($otherResultSet->getColumnTables());
		$this->setColumnTableAliases($otherResultSet->getColumnTableAliases());
		$this->setColumnTypes($otherResultSet->getColumnTypes());
		$this->setColumnDefaultValues($otherResultSet->getColumnDefaultValues());
		$this->setColumnFunctions($otherResultSet->getColumnFunctions());
		$this->colFuncsExecuted=$otherResultSet->colFuncsExecuted;
	}
	
	// Adds a Column to the ResultSet 
	function addColumn($colName, $colAlias, $colTable, $colTableAlias, $colType, $colDefaultValue, $colFunc, $value, $setValues=true) 
	{
		$this->colNames[]=$colName;
		$colNr=count($this->colNames)-1;
		
		$this->colAliases[$colNr]=$colAlias;
		$this->colTables[$colNr]=$colTable;
		$this->colTableAliases[$colNr]=$colTableAlias;
		$this->colTypes[$colNr]=$colType;
		$this->colDefaultValues[$colNr]=$colDefaultValue;
		$this->colFuncs[$colNr]=$colFunc;
		$this->colFuncsExecuted[$colNr]=false;
	
		if($setValues) 
		{
			$rowCount=count($this->rowIds);
			for($i=0;$i<$rowCount;++$i) 
			{
				$this->rows[$this->colNames[$colNr]][$i]=$value;
			}
		} 
		else 
		{
			// set values to an empty string or we will mess up the ResultSet
			$rowCount=count($this->rowIds);
			for($i=0;$i<$rowCount;++$i) 
			{
				$this->rows[$this->colNames[$colNr]][$i]="";
			}		
		}
	}
	
	// Duplicates the column $colNr (column attributes and values are duplicated)
	// not used at the moment
	function duplicateColumn($colNr)
	{
		$this->colNames[]=$this->colNames[$colNr];
		$this->colAliases[]=$this->colAliases[$colNr];
		$this->colTables[]=$this->colTables[$colNr];
		$this->colTableAliases[]=$this->colTableAliases[$colNr];
		$this->colTypes[]=$this->colTypes[$colNr];
		$this->colDefaultValues[]=$this->colDefaultValues[$colNr];
		$this->colFuncs[]=$this->colFuncs[$colNr];
		$this->colFuncsExecuted[]=$this->colFuncsExecuted[$colNr];
		
		$newColNr=count($this->colNames)-1;
	
		$rowCount=count($this->rowIds);
		for($i=0;$i<$rowCount;++$i) 
		{
			$this->rows[$this->colNames[$newColNr]][$i]=$this->rows[$this->colNames[$colNr]][$i];
		}
	}
	
	// Copies the column header-data and column-values from $srcColNr to $destColNr
	// not used at the moment
	function copyColumn($srcColNr, $destColNr)
	{
		$this->colNames[$destColNr]=$this->colNames[$srcColNr];
		$this->colAliases[$destColNr]=$this->colAliases[$srcColNr];
		$this->colTables[$destColNr]=$this->colTables[$srcColNr];
		$this->colTableAliases[$destColNr]=$this->colTableAliases[$srcColNr];
		$this->colTypes[$destColNr]=$this->colTypes[$srcColNr];
		$this->colDefaultValues[$destColNr]=$this->colDefaultValues[$srcColNr];
		$this->colFuncs[$destColNr]=$this->colFuncs[$srcColNr];
		$this->colFuncsExecuted[$destColNr]=$this->colFuncsExecuted[$srcColNr];
	
		$rowCount=count($this->rowIds);
		for($i=0;$i<$rowCount;++$i) 
		{
			$this->rows[$this->colNames[$destColNr]][$i]=$this->rows[$this->colNames[$srcColNr]][$i];
		}
	}
	
	// Removes a Column from the ResultSet.
	// After removeColumn is called, the colNr's of the other Columns change !
	function removeColumn($colNr) 
	{
		// save Pos
		$tmpPos=$this->pos;
		/*
		$this->reset();
		while($this->next()) 
		{
			//array_splice ($this->rows[$this->pos]->fields, $colNr,1);
			//삭제가능한지 체크해볼 필요있음, 구조변경후 제대로 안됨.
			array_splice ($this->rows[$this->colNames][$this->pos], $colNr,1);
		}
		*/
		$this->removeColByColNr($colNr) ;
		
		// restore Pos
		$this->pos=$tmpPos;
		
		debug_print ("Removing colum nr $colNr <br>");		

		// remove in Column Data
		array_splice($this->colNames,$colNr,1);
		array_splice($this->colAliases,$colNr,1);
		array_splice($this->colTables,$colNr,1);
		array_splice($this->colTableAliases,$colNr,1);
		array_splice($this->colTypes,$colNr,1);
		array_splice($this->colDefaultValues,$colNr,1);
		array_splice($this->colFuncs,$colNr,1);
		array_splice($this->colFuncsExecuted,$colNr,1);
	}
	
	// Orders the columns (themself e.g. [Nr] [Name] [UserId] -> [Name] [Nr] [UserId])
	// by the order the columns have in the SqlQuery object
	function orderColumnsBySqlQuery(&$sqlQuery) 
	{
		$newColNames=array();
		$newColAliases=array();
		$newColTables=array();
		$newColTableAliases=array();
		$newColTypes=array();
		$newColDefaultValues=array();
		$newColFuncs=array();
		$newColFuncsExecuted=array();
		
		$colPos=-1;
		$currentColumn=-1; 
		$oldRows=$this->rows;
		$oldRowIds=$this->rowIds;
		
		$oldColUsed=create_array_fill(count($this->colNames),false);
		
		if(count($sqlQuery->colNames)==1 && $sqlQuery->colNames[0]=="*" && (!$sqlQuery->colTables[0])) {
			return true;
		}
		
		for($i=0;$i<count($sqlQuery->colNames);++$i) 
		{
			++$currentColumn;
			
			// Handling for table.*
			if($sqlQuery->colNames[$i]=="*" && $sqlQuery->colTables[$i]) 
			{
			    for($j=0;$j<count($this->colTables);++$j) 
				{
					if(	$sqlQuery->colTables[$i] && 
					   ($sqlQuery->colTables[$i]==$this->colTables[$j] || 				   
					   $sqlQuery->colTables[$i]==$this->colTableAliases[$j])) {
						
						debug_print("transfering col " . $i . " to " . $currentColumn . "<br>");
						
						$newColNames[$currentColumn]=$this->colNames[$j];
						$newColAliases[$currentColumn]=$this->colAliases[$j];
						$newColTables[$currentColumn]=$this->colTables[$j];
						$newColTableAliases[$currentColumn]=$this->colTableAliases[$j];
						$newColTypes[$currentColumn]=$this->colTypes[$j];
						$newColDefaultValues[$currentColumn]=$this->colDefaultValues[$j];
						$newColFuncs[$currentColumn]=$this->colFuncs[$j];
						$newColFuncsExecuted[$currentColumn]=$this->colFuncsExecuted[$j];
						
						$oldColUsed[$j]=true;	
						for($k=0;$k<count($oldRows);$k++) 
						{
							$this->rowIds[$k]=$oldrowIds[$k];
							$this->rows[$this->colNames[$currentColumn]][$k]=$oldRows[$this->colNames[$j]][$k];
						}			
						$currentColumn++;
					}					
				}
				$currentColumn--;
				continue;	
			}
			
			
			if( ($colPos=$this->findColNrBySqlQuery($sqlQuery,$i))==-1) {
				print_error_msg("Column '" . $sqlQuery->colNames[$i] . "' not found!! (" 
				            . $sqlQuery->colFuncs[$i] . "," .$sqlQuery->colTables[$i].",".$sqlQuery->colAliases[$i] . ")");
				return false;
			}
			debug_print("transfering col " . $colPos . " to " . $currentColumn . "<br>");
			$newColNames[$currentColumn]=$this->colNames[$colPos];
			$newColAliases[$currentColumn]=$this->colAliases[$colPos];
			$newColTables[$currentColumn]=$this->colTables[$colPos];
			$newColTableAliases[$currentColumn]=$this->colTableAliases[$colPos];
			$newColTypes[$currentColumn]=$this->colTypes[$colPos];
			$newColDefaultValues[$currentColumn]=$this->colDefaultValues[$colPos];
			$newColFuncs[$currentColumn]=$this->colFuncs[$colPos];
			$newColFuncsExecuted[$currentColumn]=$this->colFuncsExecuted[$colPos];
			
			$oldColUsed[$colPos]=true;
			
			if($this->debug) echo("count(oldRows)[".count($oldRows)."]<br>") ;
			for($j=0;$j<count($oldRows);++$j) 
			{
				$this->rowIds[$j]=$oldRowIds[$j] ;
				$this->insertRowFromRows($j, $oldRows, $j) ;
				//$this->rows[$this->colNames[$currentColumn]][$j] = $oldRows[$this->colNames[$colPos]][$j];
			}			
		}
		
		// add the remaining columns to the end 
		for($i=0;$i<count($oldColUsed);++$i) {
			if(!$oldColUsed[$i]) {
				$addColPos=count($newColNames);
				
				debug_print("transfering col " . $i . " to " . $addColPos . "<br>");
				$newColNames[$addColPos]=$this->colNames[$i];
				$newColAliases[$addColPos]=$this->colAliases[$i];
				$newColTables[$addColPos]=$this->colTables[$i];
				$newColTableAliases[$addColPos]=$this->colTableAliases[$i];
				$newColTypes[$addColPos]=$this->colTypes[$i];
				$newColDefaultValues[$addColPos]=$this->colDefaultValues[$i];
				$newColFuncs[$addColPos]=$this->colFuncs[$i];
				$newColFuncsExecuted[$addColPos]=$this->colFuncsExecuted[$i];
				
				for($j=0;$j<count($oldRows);++$j) {
					$this->rowIds[$j]->id=$oldRowIds[$j];
					$this->rows[$j][$this->colNames[$addColPos]]=$oldRows[$j][$this->colNames[$i]];
				}	
				
			}
		}

		$this->colNames=$newColNames;
		$this->colAliases=$newColAliases;
		$this->colTables=$newColTables;
		$this->colTableAliases=$newColTableAliases;
		$this->colTypes=$newColTypes;
		$this->colDefaultValues=$newColDefaultValues;
		$this->colFuncs=$newColFuncs;
		$this->colFuncsExecuted=$newColFuncsExecuted;
		
		return true;		
	}
	
	
	// In the WHERE expression might be FUNC(col), FUNC() 
	// variants which aren't listed after SELECT.
	// This function scans a WHERE-Expression and adds the columns
	// it finds.
	// Returns true if all went ok, or false on errors
	function generateAdditionalColumnsFromWhereExpr($where_expr) 
	{
		global $g_sqlSingleRecFuncs;
		
		$parser=new SqlParser($where_expr);
		$elem="";
		$colFuncs=array();
		$colNames=array();
		$colTables=array();
		$index=-1;
		
		while(!is_empty_str($elem=$parser->parseNextElementRaw())) 
		{
				// function  ?
			if(in_array(strtoupper($elem),$g_sqlSingleRecFuncs)) 
			{
				++$index;
				$colNames[$index]="";
				$colTables[$index]="";
				$colFuncs[$index]=strtoupper($elem);

				$elem=$parser->parseNextElementRaw();
				if($elem!="(") {
					print_error_msg("( expected after $elem");
					return false;
				}
		
				while(!is_empty_str($elem=$parser->parseNextElementRaw()) && $elem!=")") 
				{
					if($elem==".") 
					{
						$colTables[$index]=$colNames[$index];
						$colNames[$index]=$parser->parseNextElementRaw();
					} 
					else 
					{
						$colNames[$index] = $elem;
					}
				}//while
			}//if(in_array
		}//while
		return $this->generateAdditionalColumnsFromArrays($colNames,$colTables,$colFuncs);
	}
	
	
	// This function scans an array of full column names
	// for additional FUNC() or FUNC(col) variants
	// and adds the columns it finds.
	// Returns true if all went ok, or false on errors
	function generateAdditionalColumnsFromArray($arrFullColNames) 
	{
		$colNames=array();
		$colTables=array();
		$colFuncs=array();
		for($i=0;$i<count($arrFullColNames);++$i) 
		{
			$colNames[$i]="";
			$colTables[$i]="";
			$colFuncs[$i]="";
			split_full_colname($arrFullColNames[$i],$colNames[$i],$colTables[$i],$colFuncs[$i]);
		}
		return $this->generateAdditionalColumnsFromArrays($colNames, $colTables, $colFuncs);
	}
	
	
	// This function scans arrays of column names, tables and functions 
	// for additional FUNC() or FUNC(col) variants
	// and adds the columns it finds.
	// Returns true if all went ok, or false on errors
	function generateAdditionalColumnsFromArrays($colNames, $colTables, $colFuncs) 
	{
		if(TXTDBAPI_DEBUG) {
			debug_printb("[generateAdditionalColumnsFromArrays] Trying to add the following columns:<br>");
			print_r($colNames); echo "<br>";
			print_r($colTables); echo "<br>";
			print_r($colFuncs); echo "<br>";
		}
				
		for($i=0;$i<count($colNames);++$i) 
		{
			// does this column allready exist ?
			$colNr = $this->findColNrByAttrs($colNames[$i], $colTables[$i], $colFuncs[$i]);
			if($colNr!=NOT_FOUND) {
				debug_print("Column <b>" . $colNames[$i] . ", " . $colTables[$i]. ", " . $colFuncs[$i] . "</b> : allready exists!<br>");
				continue;
			}
					
			// create additional columns for non-param function
			if($colFuncs[$i] && (!$colNames[$i])) 
			{
				debug_print("Column <b>" . $colNames[$i] . ", " . $colTables[$i]. ", " . $colFuncs[$i] . "</b> : creating additional Non-param-func column!<br>");
				
				$this->addColumn("","","","","str","",$colFuncs[$i],"",false);
			
			
			// create additional column for non-column-param function
			} 
			else if($colFuncs[$i] && $colNames[$i] && ( is_numeric($colNames[$i]) || has_quotes($colNames[$i]))) 
			{
				debug_print("Column <b>" . $colNames[$i] . ", " . $colTables[$i]. ", " . $colFuncs[$i] . "</b> : creating additional Non-column-param-func column!<br>");
				$this->addColumn($colNames[$i],"","","","str","",$colFuncs[$i],"",false);
			} 
			else if($colFuncs[$i] && $colNames[$i]) 
			{
				
				debug_print("Column <b>" . $colNames[$i] . ", " . $colTables[$i]. ", " . $colFuncs[$i] . "</b> : creating additional Param-func column!<br>");

				// search column (without function)
				$colNr=$this->findColNrByAttrs($colNames[$i],$colTables[$i],"");
				if($colNr==NOT_FOUND) {
					debug_print("Original NOT found!<br>");
					print_error_msg("Column '".$colNames[$i]."' not found");
					return NOT_FOUND;
				}
				debug_print("Original found at $colNr<br>");
				
				// add column
				$this->addColumn($this->colNames[$colNr], $this->colAliases[$colNr], $this->colTables[$colNr], $this->colTableAliases[$colNr], "str", "", $colFuncs[$i], "", false);
				$newCol=count($this->colNames)-1;
				// set function for new column
				$this->colFuncs[$newCol]=$colFuncs[$i];
			
			// add direct values ( no function)
			} 
			else if( !$colFuncs[$i] && $colNames[$i] && ( is_numeric($colNames[$i]) || has_quotes($colNames[$i]))) 
			{
				debug_print("Column <b>" . $colNames[$i] . ", " . $colTables[$i]. ", " . $colFuncs[$i] . "</b> : creating direct value column!<br>");
			   	$value=$colNames[$i];
			   	if(has_quotes($value)) {
			   		remove_quotes($value);
			   	}
			   	$this->addColumn($colNames[$i],"","","","str","","",$value,true);
				
			}
		}
	}
	
	
  	/***********************************
	Row Size Functions (Field Count per Row)
	************************************/
	function getRowSize() {
		if(count($this->colNames)>0)
			return count($this->colNames);
		else
			return 0;
	}
	
	/***********************************
			Row Count Functions
	************************************/
  	function getRowCount() 
	{
		if($this->debug) echo("<u>getRowCount</u> BEGIN<br>") ;
		$rowCount = 0 ;
		for($i = 0; $i < count($this->rowIds); ++$i)
		{
			if(!$this->rowNrDeleted($this->pos))
				++$rowCount ;
		}
		return $rowCount ;
 	}
 	
 	
 	/***********************************
			Field Access Functions
	************************************/
 	
 	// Get Value by Name
 	function getCurrentValueByName($colName) {
 		if(($colNr=$this->findColNrByFullName($colName))==-1)	
 			return;
 		else 			
 			return $this->rows[$this->colNames[$colNr]][$this->pos];
 	}
 	function getValueByName($rowNr,$colName) {
 		 if(($colNr=$this->findColNrByFullName($colName))==-1)	
 			return;
 		else
 			return $this->rows[$this->colNames[$colNr]][$rowNr];
 	}
 	
 	// Get Value by Nr
 	function getCurrentValueByNr($colNr) {
 		return $this->rows[$this->colNames[$colNr]][$this->pos];
 	}
 	function getValueByNr($rowNr, $colNr) {
 		return $this->rows[$this->colNames[$colNr]][$rowNr];
 	}
 	
 	// Set Value by Name 
 	function setCurrentValueByName($colName, $value) 
	{
		if($this->debug) echo("<u>setCurrentValueByName()</u>,colName[$colName], value[$value]<br>") ;
 		if(($colNr=$this->findColNrByFullName($colName))==NOT_FOUND)
		{
 			print_error_msg("Column '$colName' not found!");
 			return false;
 		} 
		else 
		{
			if($this->debug) echo("<u>setCurrentValueByName()</u>,colNr[$colNr],this->pos[{$this->pos}]value[$value]<br>") ;
 			$this->rows[$this->colNames[$colNr]][$this->pos] = $value;
 			return true;
 		}
 	}

 	function setValueByName($rowNr,$colName,$value) 
	{
 		if(($colNr=$this->findColNrByFullName($colName))==NOT_FOUND) {
 			print_error_msg("Column '$colName' not found!");
 			return false;
 		} 
		else 
		{
 			$this->rows[$this->colNames[$colNr]][$rowNr]= $value;
 			return true;
 		}
 	}
 	
 	// Set Value by Nr 
 	function setCurrentValueByNr($colNr, $value) 
	{
		$this->rows[$this->colNames[$colNr]][$this->pos] = $value;

 	}
 	function setValueByNr($rowNr, $colNr, $value) {
 		$this->rows[$this->colNames[$colNr]][$rowNr] = $value;
 	}
 	
 	// Get whole row
	function getCurrentValues() 
	{
		if($this->debug) echo("getCurrentValues(),this->pos[{$this->pos}]<br>") ;
		$row = array() ;
		for($i = 0; $i < count($this->colNames); ++$i)
		{
			$colName = $this->colNames[$i] ;
			//혹시 이름으로 한열을 접근 할 수 있으므로 번호별, 이름별로 복사해서 리턴한다.
			$row[$colName] = $this->rows[$colName][$this->pos] ;
			$row[$i] = $this->rows[$colName][$this->pos] ;
			if($this->debug) echo("getCurrentValues(),colName[$i]={$this->colNames[$i]},this->rows[colName][this->pos]=[{$this->rows[$this->colNames[$i]][$this->pos]}]<br>") ;
			//$row[$i] = $this->rows[$this->colNames[$i]][$this->pos] ;
		}
		if($this->debug)
		{
			echo("<pre>getCurrentValues\n") ;
			print_r($row) ;
			echo("</pre>") ;
		}
		//		return $this->rows[$this->pos][$this->colNames];
		return $row ;
	}

	function getValues($rowNr) 
	{
		$row = array() ;
		for($i = 0; $i < count($this->colNames); ++$i)
		{
			//혹시 이름으로 한열을 접근 할 수 있으므로 번호별, 이름별로 복사해서 리턴한다.
			$row[$this->colNames[$i]] = $rows[$this->colNames[$i]][rowNr] ;
			//$row[$i] = $this->rows[$this->colNames[$i]][rowNr] ;
		}
		return $row ;
		//	return $this->rows[$rowNr][$this->colNames];
	}
	
	// Get whole row as hash
	function getCurrentValuesAsHash()  
	{
		$row = array() ;
		for($i = 0; $i < count($this->colNames); ++$i)
		{
			//혹시 이름으로 한열을 접근 할 수 있으므로 번호별, 이름별로 복사해서 리턴한다.
			$row[$this->colNames[$i]] = $rows[$this->colNames[$i]][rowNr] ;
			$row[$i] = $rows[$this->colNames[$i]][rowNr] ;
		}

		foreach ($row as $key => $value) 
		{
			$newhash[$this->colNames[$key]]=$value; 
		}
		return $newhash; 
	}
	
	// Set whole row
	function setCurrentValues($values) 
	{
		for($i = 0; $i < count($this->colNames); ++$i)
		{
			$this->rows[$this->colNames[$i]][$this->pos] = $values[$this->colNames[$i]] ;
			//$this->rows[$i][$this->pos] = $values[$i] ; // 이게 필요할 까?
		}
		//$this->rows[$this->colNames][$this->pos] = $values;
	}

	function setValues($rowNr,$values) 
	{
		for($i = 0; $i < count($this->colNames); ++$i)
		{
			$this->rows[$this->colNames[$i]][$rowNr] = $values[$this->colNames[$i]] ;
		}
		//$this->rows[$rowNr]=$values;
	}

	// Appends a row by using an array of values
	// Here inc values wont be set, caller must supply all values !
	function appendRow($values, $id=-1) 
	{
		if($this->debug)
		{
			echo("<pre>appendRow(),values\n") ;
			print_r($values) ;
			echo("</pre>") ;
		}

		if(count($values)==count($this->getColumnNames()))
			$setDefaults = false;
		else                                                                      
			$setDefaults = true;  

		if($this->debug) echo("appendRow(), id[$id]<br>") ;
		// if id is -1 do a simple append
		if($id==-1) 
		{
			if($this->debug) echo("appendRow(), <u>simple append</u><br>") ;
			$this->append($setDefaults);
			$this->insertRowByPos($this->pos, $values) ;
			$this->rowIds[$this->pos] = $id;

			//$this->rows[$this->pos][$this->colNames]=$values;
		// else, if the id exists let the ResultSet untouched..
		} 
		else if($this->searchRowById($id)==-1) 
		{
			if($this->debug) echo("appendRow(), searchRowById is -1 append<br>") ;
			$this->append($setDefaults);
			$this->insertRowByPos($this->pos, $values) ;
			$this->rowIds[$this->pos] = $id;

			//$this->rows[$this->pos][$this->colNames]=$values;
			//$this->rows[$this->pos]->id=$id;
		} 
		else
		{
			if($this->debug) echo("appendRow(), noAppend<br>") ;
		}
		if($this->debug) echo("appendRow(), this->pos[$this->pos] this->rowIds[this->pos]=[{$this->rowIds[$this->pos]}],count_rowIds[".count($this->rowIds)."] <br>") ;

	}
	
	/***********************************
			Row Delete Functions
	************************************/

	//added by lovjesus
	function currentDeleted()
	{
		if($this->debug) echo("<u>currentDeleted()</u>,this->rowIds[$this->pos] is {$this->rowIds[$this->pos]}<br>") ;
		if($this->rowIds[$this->pos] == ROW_DELETED)
			return true ;
		else
			return false ;
	}

	function rowNrDeleted($rowNr)
	{
		if($this->rowIds[$rowNr] == ROW_DELETED)
			return true ;
		else
			return false ;
	}

	function deleteRow($rowNr) 
	{
		if($this->debug) echo("deleteRow(), rowNr[$rowNr] <br>") ;

		$this->rowIds[$rowNr] = ROW_DELETED;
		//array_splice ($this->rows, $rowNr,1);
	}
	function deleteCurrentRow() 
	{
		$this->deleteRow($this->pos);
	}
	function deleteAllRows() 
	{
		$this->rows=array();
	}
	
	/***********************************
		 	Limit Functions
	************************************/
	
	// Limit's the ResultSet
	function limitResultSet($ar_limit) 
	{
		if(!isset($ar_limit[0]) && !isset($ar_limit[1])) return $this ;
		if(count($ar_limit) == 1) 
		{
			$ar_limit[1] = $ar_limit[0];   // because LIMIT 30 is equal to
			$ar_limit[0] = 0;              // LIMIT 0,30
		}
		
		$rowCount = $this->getRowCount() ;
		if ($ar_limit[0]+$ar_limit[1] > $rowCount)
			$ar_limit[1] = $rowCount - $ar_limit[0];

		$rs=new ResultSet();
		$rs->copyColumData($this);
		
		$this->pos = $ar_limit[0];         // we begin at the offset

		for($i=0; $i<$ar_limit[1]; ++$i) 
		{
			$rs->append(0);
			for($j=0; $j < sizeof($this->colNames); ++$j)
			{
				$rs->rows[$this->colNames[$j]][$rs->pos]=$this->rowws[$this->colNames[$j]][$this->pos];
			}
			$rs->rowIds[$rs->pos] = $this->rowIds[$this->pos] ;
			//$rs->rows[$rs->pos][$this->colNames]=$this->rows[$this->pos][$this->colNames];
			//$rs->rows[$rs->pos]->id=$this->rows[$this->pos]->id;
			$this->next();
		}
		return $rs;
	}

	
	/***********************************
			Group Functions
	************************************/
	
	// Groups the ResultSet by using the groupColumns in $sqlQuery
	// and $this->colFuncs.
	// If $useLimit is true $sqlQuery->limit is used to stop grouping
	// after the result contains enough rows 
	function groupRows(&$sqlQuery,$useLimit=false) 
	{
		debug_printb("[groupRows] Grouping rows...<br>");
		global $g_sqlGroupingFuncs;
		$ar_limit=$sqlQuery->limit;
		$groupColumns=$sqlQuery->groupColumns;
		$groupColNrs=array();
		
		// use column numbers (faster)
		for($i=0;$i<count($groupColumns);++$i) 
		{
			$groupColNrs[$i]=$this->findColNrByFullName($groupColumns[$i]);
			if($groupColNrs[$i]==NOT_FOUND) 
			{
				print_error_msg("Column '" . $groupColumns[$i] . "' not found!");
				return false;
			}
			
		}
		
		// calc limit
		if(!$useLimit) 
		{
			$limit = -1;
		} 
		else 
		{
			if(!isset($ar_limit[0]) && !isset($ar_limit[1])) 
			{
				$limit = -1;
			} 
			else if(count($ar_limit) > 1) 
			{
				$limit = $ar_limit[0]+$ar_limit[1];	
			} 
			else 
			{
				$limit = $ar_limit[0];
			}
		}
			
		$rs=new ResultSet();
		$rs->copyColumData($this);
		$groupedRows=array(); 
		$groupedValues=array();
		
		$colNamesCount=count($this->colNames);
		
		$this->reset();
		while((++$this->pos)<count($this->rowIds)) 
		{
			// generate key
			$currentValues=array();
			foreach($groupColNrs as $groupColNr) 
			{
				array_push($currentValues, md5($this->rows[$this->colNames[$groupColNr]][$this->pos]));
			}
			$groupedRecsKey=join("-",$currentValues);
			
			for($i=0;$i<$colNamesCount;++$i) 
			{
				$groupedValues[$groupedRecsKey][$i][]=$this->rows[$this->colNames[$i]][$this->pos];
			}
			
			// key doesn't exist ? add record an set key into array
			if(!array_key_exists($groupedRecsKey, $groupedRows)) 
			{
				$groupedRows[$groupedRecsKey] = 1;
				$rs->append(false);			
				for($j=0; $j < sizeof($this->colNames); ++$j)
				{
					$rs->rows[$this->colNames[$j]][$rs->pos]=$this->rowws[$this->colNames[$j]][$this->pos];
				}
				$rs->rowIds[$rs->pos] = $this->rowIds[$this->pos] ;

				//$rs->rows[$rs->pos][$this->colNames]=$this->rows[$this->pos][$this->colNames];
				//$rs->rows[$rs->pos]->id=$this->rows[$this->pos]->id;
			}
			
			if($limit != -1)
				if(count($rs->rows) >= $limit)
					break;
		}
		--$this->pos;
		
		if(TXTDBAPI_VERBOSE_DEBUG) 
		{
			echo "<b>RS dump in groupRows():<br></b>";
			$rs->dump();
		}
		
		$groupFuncSrcColNr = -1; // the source column for the column with grouping functions
		
		// calculate the result of the functions
		for($i=0;$i<count($rs->colFuncs);++$i) 
		{
			if(in_array($rs->colFuncs[$i],$g_sqlGroupingFuncs)) 
			{
				if(TXTDBAPI_DEBUG) 
				{		
					debug_print("Searching source for grouping function " . $rs->colFuncs[$i] . "(");
					if($rs->colTables[$i])
						debug_print($rs->colTables[$i] . ".");
					debug_print($rs->colNames[$i] . "): ");
				}
					
				$groupFuncSrcColNr=$this->findColNrByAttrs($rs->colNames[$i],$rs->colTables[$i],"");
				if($groupFuncSrcColNr==NOT_FOUND) 
				{
					print_error_msg("Column " . $rs->colNames[$i] . ", " . $rs->colTables[$i] . " not found!");
					return null;
				}
				
				foreach($groupedValues as $key => $value) 
				{
					$groupedValues[$key][$i][0]=execGroupFunc($rs->colFuncs[$i], $groupedValues[$key][$groupFuncSrcColNr]);
				}
			}
		}
		
		// put the results back
		$rs->reset();
		foreach($groupedValues as $key => $value) 
		{
			$rs->next();
			for($i=0;$i<$colNamesCount;++$i) 
			{
				$rs->rows[$this->colNames[$i]][$rs->pos]=$groupedValues[$key][$i][0];
			}
		}
		return $rs;
	}
	
	
	// Make's the ResultSet containg only unique values
	function makeDistinct($ar_limit) 
	{
		$colNames = $this->getColumnNames();
		// calc limit
		if(!isset($ar_limit[0]) && !isset($ar_limit[1]))
			$limit = -1;
		else if(count($ar_limit) > 1) 
			$limit = $ar_limit[0]+$ar_limit[1];	
		else 
			$limit = $ar_limit[0];

		$rs=new ResultSet();
		$rs->copyColumData($this);

		$distinctRows=array();
		$this->reset();
		while((++$this->pos)<count($this->rowIds)) {
			$currentValues=array();
			foreach($colNames as $col)
				array_push($currentValues, md5($this->getCurrentValueByName($col)));
			$joinedValues=join("-",$currentValues);
			if(!array_key_exists($joinedValues, $distinctRows)) {
				$distinctRows[$joinedValues] = 1;
				$rs->append(false);

				for($j=0; $j < sizeof($this->colNames); ++$j)
				{
					$rs->rows[$this->colNames[$j]][$rs->pos]=$this->rowws[$this->colNames[$j]][$this->pos];
				}
				$rs->rowIds[$rs->pos] = $this->rowIds[$this->pos] ;
				//$rs->rows[$rs->pos][$this->colNames]=$this->rows[$this->pos][$this->colNames];
				//$rs->rows[$rs->pos]->id=$this->rows[$this->pos]->id;
			}
			if($limit != -1)
				if($rs->getRowCount() >= $limit)
					break;
		}
		--$this->pos;
		return $rs;
	}


	
	/***********************************
			Filter Functions
	************************************/

	// Removes all columns which are not found in the SqlQuery object
	function filterByColumnNamesInSqlQuery(&$sqlQuery) 
	{
		$colNrsToKeep=array();
		
		if(count($sqlQuery->colNames)==1 && $sqlQuery->colNames[0]=="*" && (!$sqlQuery->colTables[0]))
			return true;
		
		for($i=0;$i<count($sqlQuery->colNames);++$i) 
		{
			
			// keep all of a table ?
			if($sqlQuery->colNames[$i]=="*" && $sqlQuery->colTables[$i]) 
			{
			  	$keepAllOfTable=$sqlQuery->colTables[$i];
			  	for($j=0;$j<count($this->colTables);++$j) 
				{
					if($this->colTables[$j]==$keepAllOfTable || $this->colTableAliases[$j]==$keepAllOfTable) 
					{
						$colNrsToKeep[]=$j;
					}
				}  	
			} 
			else 
			{
				$colNr=$this->findColNrBySqlQuery($sqlQuery,$i);
				if($colNr==NOT_FOUND) 
				{
					print_error_msg("filterByColumnNames(): Column '" . $sqlQuery->colNames[$i] . "' not found");
					return null;
				} 
				else 
				{
					$colNrsToKeep[]=$colNr;
				}
			}
		}
		
		// remove from last element to first (because colNr's change afer a removeColumn() call
		for($i=count($this->colNames)-1;$i>=0;$i--) 
			if(!in_array($i,$colNrsToKeep))
				$this->removeColumn($i);
	}
	
	
	

	// Filters the rows by 1-n AND Conditions
	// parameters: 
	// 2 parameter arrays and 1 operator array
	// the entry in the parameter arrays can be columns or 
	// values (numbers: 1234 or strings: 'bla')
	// Return value: ResultSet with filtered Records (copy) ($this is left unchanged)
	function filterRowsByAndConditions($params1, $params2, $operators) {
		$rs=new ResultSet();
		$rs->copyColumData($this);
		
		$this->reset();
		
		$colNrs1=array();
		$colNrs2=array();
		
		// find column nr's for params1 -1=no column, its a direct value
		for($i=0;$i<count($params1);++$i) {
			if(($colNrs1[$i]=$this->findColNrByFullName($params1[$i]))==NOT_FOUND) {
				if(has_quotes($params1[$i]) || is_numeric($params1[$i])) {
					$colNrs1[$i]=-1;
					if(has_quotes($params1[$i])) {
						remove_quotes($params1[$i]);
					}
				} else {
					print_error_msg("Column '" . $params1[$i] . "' not found");
					return null;
				}
			}
		}
		
		// find column nr's for params2 -1=no column, its a direct value
		for($i=0;$i<count($params2);++$i) {
			if(($colNrs2[$i]=$this->findColNrByFullName($params2[$i]))==NOT_FOUND) {
				if(has_quotes($params2[$i]) || is_numeric($params2[$i])) {
					$colNrs2[$i]=-1;
					if(has_quotes($params2[$i])) {
						remove_quotes($params2[$i]);
					}
				} else {
					print_error_msg("Column '" . $params2[$i] . "' not found");
					return null;
				}
			}
		}
		
		$val1="";
		$val2="";			
		$this->reset();
		while((++$this->pos)<count($this->rowIds)) {
			$recMetsConds=true;
			for($i=0;$i<count($params1);++$i) {
				
				if($colNrs1[$i]==-1) {
					$val1=$params1[$i];
				} else {
					$val1=$this->rows[$this->colNames[$colNrs1[$i]]][$this->pos];
				}
				
				if($colNrs2[$i]==-1) {
					$val2=$params2[$i];
				} else {
					$val2=$this->rows[$this->colNames[$colNrs2[$i]]][$this->pos];
				}
				
				if(!compare($val1,$val2,$operators[$i])) {
					$recMetsConds=false;
					break;
				}
			}
			
			if($recMetsConds) 
			{
				$rs->append(false);
			
				for($j=0; $j < sizeof($this->colNames); ++$j)
				{
					$rs->rows[$this->colNames[$j]][$rs->pos]=$this->rows[$this->colNames[$j]][$this->pos];
				}
				$rs->rowIds[$rs->pos] = $this->rowIds[$this->pos] ;

				//$rs->rows[$rs->pos][$this->colNames]=$this->rows[$this->pos][$this->colNames];
				//$rs->rows[$rs->pos]->id=$this->rows[$this->pos]->id;
			}
		}
		// reset ResultSet's
		$this->reset();
		$rs->reset();
		return $rs;
	}

	
	// Removes all rows from $this, which are not contained
	// in $otherResultSet. 
	// The $rows->id var is used to check if 2 Rows match.
	// parameter: $otherResultSet with !! row->id's set !!
	function filterResultSetAndWithAnother(&$otherResultSet) 
	{	
		$this->reset();
		while($this->next()) 
		{
			if($otherResultSet->searchRowById($this->getCurrentRowId())==NOT_FOUND) 
			{
				$this->deleteCurrentRow();
				$this->prev(); // Because the current Row was deleted, check again at this position
			}
		}		
	}
	

	/***********************************
			ResultSet join Functions
	************************************/

	// Returns a ResultSet which contains the columns and rows
	// of $this and $otherResultSet (a new ResultSet is returned).
	// The ResultSet itself ($this) is left unchanged.
	// For each row in $this each row in $otherResultSet will be duplicated
	// Example:
	// 1	Test	Hello
	// 2 	Test2	Hello2 
	//  joined with
	// 10	Blabla
	// 11 	Foo_Bar
	// 13   Bar_foo
	//  results in
	// 1	Test	Hello	10	Blabla
	// 1	Test	Hello	11	Foo_Bar
	// 1	Test	Hello	13	Bar_foo
	// 2 	Test2	Hello2 	10	Blabla
	// 2 	Test2	Hello2 	11	Foo_Bar
	// 2 	Test2	Hello2 	13	Bar_foo
	//
	function joinWithResultSet(&$otherResultSet) 
	{
		if($this->debug) echo("joinWithResultSet BEGIN<br>") ;
		if($this->getRowCount()<1) {
			debug_print("Joining emtpy ResultSet (results in empty ResultSet)");
		}
			
		$newResultSet=new ResultSet();
		// columns
		$newResultSet->setColumnNames(array_merge ($this->getColumnNames(), $otherResultSet->getColumnNames()));
		$newResultSet->setColumnAliases(array_merge ($this->getColumnAliases(), $otherResultSet->getColumnAliases()));
		$newResultSet->setColumnTables(array_merge ($this->getColumnTables(), $otherResultSet->getColumnTables()));
		$newResultSet->setColumnTableAliases(array_merge ($this->getColumnTableAliases(), $otherResultSet->getColumnTableAliases()));
		$newResultSet->setColumnTypes(array_merge ($this->getColumnTypes(), $otherResultSet->getColumnTypes()));
		$newResultSet->setColumnDefaultValues(array_merge ($this->getColumnDefaultValues(), $otherResultSet->getColumnDefaultValues()));
		$newResultSet->setColumnFunctions(array_merge ($this->getColumnFunctions(), $otherResultSet->getColumnFunctions()));
		$newResultSet->colFuncsExecuted=(array_merge ($this->colFuncsExecuted, $otherResultSet->colFuncsExecuted));
		
		$otherResultSet->reset();
		$this->reset();
		$newResultSet->reset();
		
		while($this->next()) 
		{
			$otherResultSet->reset();
			while($otherResultSet->next()) 
			{
				$row=array_merge($this->getCurrentValues(),$otherResultSet->getCurrentValues());
				$newResultSet->appendRow($row);
			}
		}
		return $newResultSet;
	}	
	
	
	/***********************************
			Row Order Functions
	************************************/
	// Order the rows in the ResultSet 
	// Parameters:
	// $orderCols an array of full column names to order
	// $orderTypes type of order for the column (ORDER_ASC or ORDER_DESC)
	// returns false on errors
	// 정렬 알고리즘 변경 by lovjesus 2004/01/06(Tue)
	function orderRows($orderCols,$orderTypes) 
	{
		// return if the ResultSet size is 0
		if(count($this->rowIds)<1)
			return;

		$colNrs=array();
		for($i=0;$i<count($orderCols);++$i) 
		{
			if(($colNrs[$i]=$this->findColNrByFullName($orderCols[$i]))==NOT_FOUND) 
			{
				print_error_msg("orderRows(): Column '" . $orderCols[$i] . "' not found");
				return false;
			}
		}

		$evalString="";
		for($i =0; $i < count($orderTypes); ++$i) 
		{
			$colName = $this->colNames[$colNrs[$i]] ;

			if($orderTypes[$i] == ORDER_ASC)
				$evalString .= "\$this->rows[\"".$colName."\"], SORT_ASC, ";
			else
				$evalString .= "\$this->rows[\"".$colName."\"], SORT_DESC, ";
		}
	
		$cnt_colNames = count($this->colNames) ;
		for($i=0 ;$i < $cnt_colNames; ++$i)
		{
			$colName = $this->colNames[$i] ;
			if(!in_array($i, $colNrs))
			{
				$evalString .= "\$this->rows[\"".$colName."\"]" ;
				if($i+1 <  $cnt_colNames) 
				{
					$evalString .= ", " ;
				}
			}
		}
			
		$evalString = "array_multisort(".$evalString.");";

		if($this->debug) echo("evalString[$evalString]<br>") ;
		eval($evalString);

		return true;
	}

	/***********************************
		  'SQL Functions' Functions
	************************************/
	
	// Executes all functions in the ResultSet which have no grouping behavior.
	// Only function for columns where colFuncsExecuted is false are executed.
	function executeSingleRecFuncs() 
	{
		global $g_sqlSingleRecFuncs;
			
		debug_printb("[executeSingleRecFuncs] executing singlerec functions...<br>");
		for($i=0;$i<count($this->colFuncs);++$i) 
		{
			
			if($this->debug) 
			{
				echo("<pre>colFunctions:$i-th\n") ;
				print_r($this->colFuncs[$i]) ;
				echo("</pre>") ;
			}
			if(!$this->colFuncs[$i] || $this->colFuncsExecuted[$i])
				continue;
			
			if(!in_array($this->colFuncs[$i],$g_sqlSingleRecFuncs))
				continue;
					
			debug_print($this->colFuncs[$i] . "(" . $this->colNames[$i] . "): ");
			
			// function with paramater, but the parameter is not a column	
			if($this->colNames[$i] && !is_empty_str($this->colNames[$i]) && (is_numeric($this->colNames[$i]) || has_quotes($this->colNames[$i]))) 
			{
				$param=$this->colNames[$i];
				if(has_quotes($param))
					remove_quotes($param);
				$result=execFunc($this->colFuncs[$i],$param);
				$rowCount=count($this->rowIds);
				
				debug_print("a function with a non-column parameter! (result=$result)<br>");
				for($j=0;$j<$rowCount;++$j) 
				{
					$this->rows[$this->colNames[$i]][$j]=$result;
				}
				$this->colFuncsExecuted[$i]=true;
			
			
			// function with parameter? =>execute function with the values form the original column
			} 
			else if($this->colNames[$i]) 
			{
				
				debug_print("a function with a column parameter!<br>");
				
				// find original column (without function)
				$origColNr=$this->findColNrByAttrs($this->colNames[$i], $this->colTables[$i], "");
				if($origColNr==NOT_FOUND) {
					print_error_msg("Column '" . $this->colNames[$i] . "' not found!");
					return false;
				}
				
				// copy some column header data from the original
				$this->colTables[$i]=$this->colTables[$origColNr];
				$this->colTableAliases[$i]=$this->colTableAliases[$origColNr];
					
				// apply function (use values from the original column as input)					 
				$rowCount=count($this->rowIds);
				for($j=0;$j<$rowCount;++$j) 
				{
					$this->rows[$this->colNames[$i]][$j]=execFunc($this->colFuncs[$i], $this->rows[$this->colNames[$origColNr]][$j]);
				}
				$this->colFuncsExecuted[$i]=true;
			
			// function without parameter: just execute!
			} else {
				debug_print("a function with no parameters!<br>");
				$result=execFunc($this->colFuncs[$i],"");
				$rowCount=count($this->rowIds);
				for($j=0;$j<$rowCount;++$j) {
					$this->rows[$this->colNames[$i]][$j]=$result;
				}
				$this->colFuncsExecuted[$i]=true;
			}
		}

		if($this->debug) echo("[executeSingleRecFuncs] end<br>") ;
	}
	
	
	
	
	/***********************************
			Debug Functions
	************************************/

	// Dump's the ResultSet 
	function dump() 
	{
		$size=35;
		$format="%-" . $size . "s";
		$id_size=5;
		$id_format="%-" . $id_size ."s";
		
		
		echo "<pre><b><i>ResultSet dump (Row Count: " . $this->getRowCount() . ")</b></i><br>";
		echo "<br><b>";

		printf($id_format,"ID");		
		// Column Names
		reset($this->colNames);
		while (list ($key, $val) = each ($this->colNames))
			printf($format, "$val");			
		echo "</b><br>";
		
		printf($id_format,"");
		reset($this->colNames);
		while (list ($key, $val) = each ($this->colNames))  
		{
			printf($format, "(al=" .$this->colAliases[$key] . ", tbl=". $this->colTables[$key] . ", tba=" .$this->colTableAliases[$key] . ")");			
		}
		echo "<br>";
		
		printf($id_format,"");
		reset($this->colNames);
		while (list ($key, $val) = each ($this->colNames))  
		{
			printf($format, "(ty=". $this->colTypes[$key]  .", def=". $this->colDefaultValues[$key] .", fnc=". $this->colFuncs[$key] .", ex=". $this->colFuncsExecuted[$key] . ")");			
		}
		echo "<br>";

		printf("%'-" . $id_size . "s","|");
		
		for($i=0;$i<count($this->colNames);++$i)
			printf("%'-" . $size . "s","|");
		echo "<br><br>";
		
		$this->reset();
		
		if(!isset($this->rows))
			return;
		
		while($this->next()) 
		{
			/*
			//이부분 수정이 필요
			reset($this->rows[$this->pos][$this->colNames]);

			if(isset($this->rows[$this->pos]->id))
				printf($id_format,$this->rows[$this->pos]->id . ": ");
			//이부분 수정이 필요
			while (list ($key, $val) = each ($this->rows[$this->colNames][$this->pos])) 
				printf($format, "$val");			
			
			echo "<br>";
			*/
		}
		echo "</pre>";
		$this->reset();
	}
}


/**********************************************************************
							ResultSetParser
***********************************************************************/

// Used to parse a ResultSet object from and into text-files
class ResultSetParser 
{
	var $debug = 0 ;
	var $profile = 1 ;
	var $escapeCodeWrite;
	var $replaceWithWrite;
	
	var $escapeCodeRead;
	var $replaceWithRead; 
	
	
	/***********************************
			Line Parse Functions
	************************************/
	
	function ResultSetParser() 
	{
		$this->escapeCodeRead=array(TABLE_FILE_ESCAPE_CHAR."h", 
									TABLE_FILE_ESCAPE_CHAR."n",
									TABLE_FILE_ESCAPE_CHAR."r", 
									TABLE_FILE_ESCAPE_CHAR."p");
		
		$this->replaceWithRead=array("#", "\n", "\r", TABLE_FILE_ESCAPE_CHAR);
		
		$this->escapeCodeWrite=array_reverse($this->escapeCodeRead);
		$this->replaceWithWrite=array_reverse($this->replaceWithRead);
	}

	/***********************************
			Line Parse Functions
	************************************/
	
	function parseRowFromLine($line) 
	{
		if($this->debug) echo("<u>parseRowFromLine</u><br>") ;
		if(strlen(trim($line))==0)
			return false;
		// handle Windows \x0D\x0A (\r\n) newlines
		$line=rtrim($line);
		$row=explode('#', $line);
				
		$row=str_replace($this->escapeCodeRead, $this->replaceWithRead, $row);
		
		return $row;
	}
	

	function parseLineFromRow($row) 
	{
		if($this->debug) 
		{
			echo("<u>parseLineFromRow()</u>, BEGIN<pre>") ;
			print_r($row) ;
			echo("</pre>") ;
		}
		$row=str_replace($this->replaceWithWrite, $this->escapeCodeWrite, $row);
		if($this->debug) 
		{
			echo("<u>parseLineFromRow()</u>,<pre>") ;
			print_r($row) ;
			echo("</pre>") ;
		}

		$return_row = implode("#", $row) ;

		if($this->debug) 
		{
			echo("<u>parseLineFromRow()</u>,<pre>") ;
			print_r($return_row) ;
			echo("</pre>") ;
		}
		return $return_row ;
			
	}

	/***********************************
			File Parse Functions
	************************************/
	// $fd must be a file descriptor (returned by fopen)
	function parseResultSetFromFile($fd) 
	{
		$rs = new ResultSet();
		$buf="";
		// read in the whole file
		fseek($fd,0,SEEK_END);
		$size=ftell($fd);
		fseek($fd,0,SEEK_SET);
		$wholeFile=fread($fd,$size);
				

		$lines=explode("\n",$wholeFile);
		unset($wholeFile); 
		$wholeFile="";
		
		$rec=$this->parseRowFromLine($lines[0]);
   		$rs->setColumnNames($rec);
   		
   		$rec=$this->parseRowFromLine($lines[1]);
   		$rs->setColumnTypes($rec);
   		
   		$rec=$this->parseRowFromLine($lines[2]);
   		$rs->setColumnDefaultValues($rec);


   		$rs->reset();
		if($this->profile) {$start_time[0] = getmicrotime() ;}		

		//LIMIT옵션을 여기서 처리하도록 하자. 2004/02/13
		$start_offset = 3 ;
		$lineCount=count($lines);
		$end_offset = $lineCount ;

		//$lineCount = 10 ; // for debug 
  		for($i=$start_offset;$i<$end_offset;++$i) 
		{
  			//$rec=$this->parseRowFromLine($lines[$i]);		
  			//inlining function parseRowFromLine() for better performance
  			//$line = $lines[$i];
            if(strlen(trim($lines[$i]))==0) continue;

            // handle Windows \x0D\x0A (\r\n) newlines
            $lines[$i]=rtrim($lines[$i]);
            $rec=explode('#', $lines[$i]);
            $rec=str_replace($this->escapeCodeRead, $this->replaceWithRead, $rec);
			/*
			if(count($rec)==count($rs->colNames))
                $setDefaults = false;
            else                                                                      
                $setDefaults = true;  
			*/
        	if($rec) 
			{
				/*
            	$rs->append($setDefaults, $i-$start_offset);
				$rs->insertRowByPos($rs->pos, $rec) ;
				*/
		 		$rs->pos= $i - $start_offset;
				$colName_cnt = count($rs->colNames) ;
				for($j = 0; $j < $colName_cnt; ++$j)
				{
					$rs->rows[$rs->colNames[$j]][$rs->pos] = $rec[$j] ;
				}
				$rs->rowIds[$rs->pos]= -1;
  			}
  		}
		if($this->profile) echo("<b>in [parseResultSetFromFile] time: $lineCount</b><i><u>".getexectime($start_time[0])."</u> seconds</i><br>") ;
		
  		$rs->setColumnAliases(create_array_fill(count($rs->colNames),""));
  		$rs->setColumnTables(create_array_fill(count($rs->colNames),""));
  		$rs->setColumnTableAliases(create_array_fill(count($rs->colNames),""));
  		$rs->setColumnFunctions(create_array_fill(count($rs->colNames),""));
  		$rs->colFuncsExecuted=create_array_fill(count($rs->colNames),false);
	
		return $rs;	
	}
	
	
	// $fd must be a file descriptor (returned by fopen)
	function parseResultSetIntoFile($fd, &$resultSet) 
	{
    
    	debug_print( "parseResultSetIntoFileFD<br>");
		fwrite($fd, $this->parseLineFromRow($resultSet->getColumnNames()));
		fwrite($fd, "\n");
		
		fwrite($fd, $this->parseLineFromRow($resultSet->getColumnTypes()));
		fwrite($fd, "\n");
		
		fwrite($fd, $this->parseLineFromRow($resultSet->getColumnDefaultValues()));
		fwrite($fd, "\n");
		
		$resultSet->reset();
		while($resultSet->next()) 
		{
			$line = "" ;
			if(!$resultSet->currentDeleted())  
			{
				$line = $this->parseLineFromRow($resultSet->getCurrentValues()) ;
				if($this->debug) echo("<u>parseResultSetIntoFile()</u>,line[$line]<br>") ;
				fwrite($fd, $line);
			}
			
			if($resultSet->getPos()<$resultSet->getRowCount()-1)
				fwrite($fd, "\n");
		}		
	}
	
	
	// $fd must be a file descriptor (returned by fopen)
	// Parses only the column names, data types, default values and 
	// some of the last rows so the ResultSet can be used to append records.
	function parseResultSetFromFileForAppend($fd) 
	{
		$start=getmicrotime();
		$rs=new ResultSet();
		
		// COLUMN NAMES
		// read with a maximum of 1000 bytes, until there is a newline included (or eof)
		$buf="";
		while(is_false(strstr($buf,"\n"))) 
		{
		    $buf.=fgets($fd,1000);
		    if(feof($fd)) 
			{
		        print_error_msg("Invalid Table File!<br>");
		        return null;
		    }
		}
		// remove newline
		remove_last_char($buf);
		
		$rec=$this->parseRowFromLine($buf);
   		$rs->setColumnNames($rec);
   		// COLUMN TYPES
   		// read with a maximum of 1000 bytes, until there is a newline included (or eof)
   		$buf="";
		while(is_false(strstr($buf,"\n"))) 
		{
		    $buf.=fgets($fd,1000);
		    if(feof($fd)) 
			{
				print_error_msg("Invalid Table File!<br>");
		        return null;
		    }
		}
		// remove newline
		remove_last_char($buf);
			
		$rec=$this->parseRowFromLine($buf);
   		$rs->setColumnTypes($rec);
   		
   		
   		// COLUMN DEFAULT VALUES
   		// read with a maximum of 1000 bytes, until there is a newline included (or eof)
   		$buf="";
		while(is_false(strstr($buf,"\n"))) {
		    $buf.=fgets($fd,1000);
		    if(feof($fd)) {
		        break; // there's no newline after the colum types => empty table
		    }
		}
		
		// remove newline
		if(last_char($buf)=="\n")
			remove_last_char($buf);
			
		$rec=$this->parseRowFromLine($buf);
   		$rs->setColumnDefaultValues($rec);
   		
   		
   		// get file size		
		fseek($fd,0,SEEK_END);
		$size=ftell($fd);
		$lastRecSize=min($size,ASSUMED_RECORD_SIZE);
		
		$lastRecPos=false;
		while(is_false($lastRecPos)) 
		{
		    fseek($fd,-$lastRecSize,SEEK_END);
		    $buf=fread($fd,$lastRecSize);
		    $lastRecSize=$lastRecSize*2;
		    $lastRecSize=min($size,$lastRecSize);
			if($lastRecSize<1) 
			{
				print_error_message("lastRecSize should not be 0! Contact developer please!");
			}
		    $lastRecPos=$this->getLastRecordPosInString($buf);
		    if(TXTDBAPI_VERBOSE_DEBUG) 
			{
		        echo "<hr>pass! <br>";
		        echo "lastRecPos: " . $lastRecPos . "<br>";
		        echo "buf: " . $buf . "<br>";
            }
		    
		    
		}		
		
		$buf=trim(substr($buf,$lastRecPos));
		
		verbose_debug_print("buf after substr() and trim(): " . $buf . "<br>");
		   		
   		$rs->reset();
   		$row=$this->parseRowFromLine($buf);
   		
   		if(TXTDBAPI_VERBOSE_DEBUG) 
		{
   		    echo "parseResultSetFromFileForAppend(): last Row:<br>";
   		    print_r($row);
   		    echo "<br>";
        }
        
   		
   		$rs->appendRow($row);	
   		
   		$rs->setColumnAliases(create_array_fill(count($rs->colNames),""));
  		$rs->setColumnTables(create_array_fill(count($rs->colNames),""));
  		$rs->setColumnTableAliases(create_array_fill(count($rs->colNames),""));
  		$rs->setColumnFunctions(create_array_fill(count($rs->colNames),""));
  		$rs->colFuncsExecuted=create_array_fill(count($rs->colNames),false);
   	
   		debug_print("<i>III: parseResultSetFromFileForAppend: " . (getmicrotime() - $start) . " seconds elapsed</i><br>");
   		
  		return $rs;	
	}
	
	
	// $fd must be a file descriptor (returned by fopen)
	function parseResultSetIntoFileAppend($fd, &$resultSet) 
	{
		if($this->debug) echo("<u>parseResultSetIntoFileAppend()</u>,BEGIN<br>") ;
    	fwrite($fd, "\n");
		$resultSet->reset();
		while($resultSet->next()) 
		{
			$line = "" ;
			if(!$resultSet->currentDeleted())
			{
				$line = $this->parseLineFromRow($resultSet->getCurrentValues()) ;
				if($this->debug) echo("<u>parseResultSetIntoFileAppend()</u>, current NOT DELETED line[$line]<br>") ;
				fwrite($fd, $line);
			}
			else
			{
				if($this->debug) echo("<u>parseResultSetIntoFileAppend()</u>, current DELETED<br>") ;
			}
			
			if($this->debug) 
			{
				echo("<u>parseResultSetIntoFileAppend()</u>,resultSet->getPos[".$resultSet->getPos()."]resultSet->getRowCount[".($resultSet->getRowCount()-1)."]<br>") ;
			}

			if($resultSet->getPos()<$resultSet->getRowCount()-1)
			{
				fwrite($fd, "\n");
			}
		}		
	}
	
	// Returns an offset into $str where the last record begins.
	// If $str doesn't contain one valid record false is returned.
	// (Attention: may also return 0, which has not the same meaning as
	// false)
	function getLastRecordPosInString($str) 
	{
	   
	    // contains other chars then whitespaces ?
	    if(strlen(trim($str))==0)
            return false;
        
        $pos=strlen($str)-1;
        
        while($str{$pos}=="\n" || $str{$pos}=="\r" || $str{$pos}=="\t" || $str{$pos}==" ") {
        	--$pos;
        	if($pos==-1)
        		return false;
        }
        while($str{$pos}!="\n" && $str{$pos}!="\r") {
        	--$pos;
        	if($pos==-1)
        		return false;
        }
        return $pos+1;
    }
	
}	

?>