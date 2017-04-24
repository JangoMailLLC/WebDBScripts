<%@LANGUAGE="VBScript" codepage=65001%>
<%
Option Explicit

' *******************COPYRIGHT NOTICE*******************
' THIS CODE IS COPYRIGHT, RESPECTIVE E-MAIL APPLICATION, 2000-2004.
' THIS CODE MAY NOT BE USED IN ANY APPLICATION OTHER THAN
' THE E-MAIL SERVICE FROM WHICH IT WAS ORIGINALLY RETRIEVED.
'
' *******************INSTRUCTIONS*******************
' THIS FILE SHOULD BE PLACED IN THE ROOT DIRECTORY OF
' YOUR WEB SERVER.
'
' *****************VERSION INFORMATION***************
' LAST UPDATED 8/25/2008

Server.ScriptTimeout = 1200

Const intConnectionTimeout = 120
Const intCommandTimeout = 1200

Const strRowDelimiter = "WG0ROWWG0"
Const strColDelimiter = "WG0COLWG0"
Const strEOF = "WANGO-ENDOFDATASTREAM"

Dim straction
Dim strdbname
Dim strmachinename
Dim strusername
Dim strpassword
Dim strquerystring
Dim cn
Dim rs
Dim intfieldcount
Dim flag
Dim field
Dim fieldnames
Dim first
Dim resultstring
Dim i

Response.Buffer = true

straction = Request.Form("action")
strdbname = Request.Form("dbname")
strmachinename = Request.Form("machinename")
strusername = Request.Form("username")
strpassword = Request.Form("password")
strquerystring = Request.Form("querystring")

straction = replace(straction,"ABC-WANGOMAIL-ABC",chr(0))
strdbname = replace(strdbname,"ABC-WANGOMAIL-ABC",chr(0))
strmachinename = replace(strmachinename,"ABC-WANGOMAIL-ABC",chr(0))
strusername = replace(strusername,"ABC-WANGOMAIL-ABC",chr(0))
strpassword = replace(strpassword,"ABC-WANGOMAIL-ABC",chr(0))
strquerystring = replace(strquerystring,"ABC-WANGOMAIL-ABC",chr(0))

Set cn = Server.CreateObject("ADODB.Connection")
cn.CommandTimeout = intCommandTimeout
cn.ConnectionTimeout = intConnectionTimeout

cn.Open "Provider=sqloledb;Data Source=" & strmachinename & ";Initial Catalog=" & strdbname & ";User Id= " & strusername & ";Password=" & strpassword

If straction = "massmail" Then

	Set rs = cn.Execute (strquerystring)

	intfieldcount = rs.fields.count

	flag = 0
	For Each field In rs.fields
	If flag = 0 Then
		fieldnames = field.name
		flag = 1
	Else
		fieldnames = fieldnames & "," & field.name
	End If
	Next

	resultstring = fieldnames & "___ASDF---BREAK"

	Do While Not rs.eof 
		For i = 0 To intfieldcount-1
			if first = 0 Then
				If instr(1,rs.fields(i),chr(0)) > 0 Then
				
					resultstring = resultstring & Replace(CStr(rs.fields(i)),chr(0),"")
				
				Else
				
					resultstring = resultstring & rs.fields(i)
				
				End If
			
				first = 1
			Else
				If instr(1,rs.fields(i),chr(0)) > 0 Then
			
					resultstring = resultstring & strColDelimiter & Replace(CStr(rs.fields(i)),chr(0),"")
					
				Else
				
					resultstring = resultstring & strColDelimiter & rs.fields(i)
				
				End If
			End if
		Next
		resultstring = resultstring & strRowDelimiter

		Response.Write (resultstring)
		Response.Flush 
		resultstring = ""

		first = 0
		rs.movenext
	Loop

	Response.Write(strEOF)
	Response.Flush
	
End If

On Error Resume Next

If straction = "unsubscribe" Then

	cn.Execute (strquerystring)
	If Err.number = 0 Then Response.Write("unsubscribe-sync-success") Else Response.Write("unsubscribe-sync-failure")

ElseIf straction = "bounce" Then

	cn.Execute (strquerystring)
	If Err.number = 0 Then Response.Write("bounce-sync-success") Else Response.Write("bounce-sync-failure")

ElseIf straction = "change" Then

	cn.Execute (strquerystring)
	If Err.number = 0 Then Response.Write("change-sync-success") Else Response.Write("change-sync-failure")

ElseIf straction = "view" Then

	cn.Execute (strquerystring)
	If Err.number = 0 Then Response.Write("view-sync-success") Else Response.Write("view-sync-failure")

ElseIf straction = "click" Then

	cn.Execute (strquerystring)
	If Err.number = 0 Then Response.Write("click-sync-success") Else Response.Write("click-sync-failure")

ElseIf straction = "sent" Then

	cn.Execute (strquerystring)
	If Err.number = 0 Then Response.Write("sent-sync-success") Else Response.Write("sent-sync-failure")

ElseIf straction = "job" Then

	cn.Execute (strquerystring)
	If Err.number = 0 Then Response.Write("job-sync-success") Else Response.Write("job-sync-failure")
	
ElseIf straction = "action" Then

	cn.Execute (strquerystring)
	If Err.number = 0 Then Response.Write("action-sync-success") Else Response.Write("action-sync-failure")
	
ElseIf straction = "forward" Then

	cn.Execute (strquerystring)
	If Err.number = 0 Then Response.Write("forward-sync-success") Else Response.Write("forward-sync-failure")	

End If

If straction = "test" Then

	Response.Write("test-success")

End If

On Error Goto 0
%>