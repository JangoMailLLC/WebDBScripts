<%@ Page Language="C#" %>
<%@ Import Namespace="System.Data.SqlClient" %>
<script runat=server>

 /*******************COPYRIGHT NOTICE*******************
 THIS CODE IS COPYRIGHT, RESPECTIVE E-MAIL APPLICATION, 2000-2009.
 THIS CODE MAY NOT BE USED IN ANY APPLICATION OTHER THAN
 THE E-MAIL SERVICE FROM WHICH IT WAS ORIGINALLY RETRIEVED.

 *******************INSTRUCTIONS*******************
 THIS FILE SHOULD BE PLACED IN THE ROOT DIRECTORY OF
 YOUR WEB SERVER.

 *****************VERSION INFORMATION***************
 LAST UPDATED 2/4/2009
 ***************************************************/

    const int ConnectionTimeout = 120;
    const int CommandTimeout = 1200;
    const string RowDelimiter = "WG0ROWWG0";
    const string ColDelimiter = "WG0COLWG0";
    const string EOF = "WANGO-ENDOFDATASTREAM";
    
    protected override void  OnInit(EventArgs e)
    {   
 	base.OnInit(e);
        Server.ScriptTimeout = 1200;
        Response.Buffer  = true;
        
        string action = GetFormVar("action");
        string dbname = GetFormVar("dbname");
        string machinename = GetFormVar("machinename");
        string username = GetFormVar("username");
        string password = GetFormVar("password");
        string SQLString = GetFormVar("SQLString");
        
        string connectionString = "Data Source=" + machinename + ";Initial Catalog=" + dbname + ";User Id= " + username + ";Password=" + password + ";Connection Timeout=" + ConnectionTimeout;
    
    
        SqlConnection conn = new SqlConnection(connectionString);
        SqlCommand cmd = null;
        SqlDataReader reader = null;
        try
        {
            conn.Open();
            
            cmd = new SqlCommand(SQLString, conn);
            cmd.CommandTimeout = CommandTimeout;
            
            if (action == "massmail")
            {
                reader = cmd.ExecuteReader(System.Data.CommandBehavior.CloseConnection);
                StringBuilder sb = new StringBuilder();
                for(int i = 0; i < reader.FieldCount; i++)
                    sb.Append((sb.Length > 0 ? "," : "") + reader.GetName(i));
                sb.Append("___ASDF---BREAK");
                
                while (reader.Read())
                {
                    for (int i = 0; i < reader.FieldCount; i++)
                    {
                        string val = reader.IsDBNull(i) ? "" : reader.GetValue(i).ToString();
                        if (i > 0)
                            sb.Append(ColDelimiter);                        
                        sb.Append(val.Replace("\0", ""));
                    }
                    sb.Append(RowDelimiter);
                    Response.Write(sb.ToString());
                    sb = new StringBuilder();
                }
		Response.Write(EOF);
                reader.Close();
            }
            else if (action == "test")
                Response.Write("test-success");
            else if (action == "unsubscribe" || action == "bounce" || action == "change" || action == "view" || 
                     action == "click" || action == "sent" || action == "job" || action == "action" || action == "forward")
            {
                try
                {
                    cmd.ExecuteNonQuery();
                    Response.Write(action + "-sync-success");
                }
                catch
                {
                    Response.Write(action + "-sync-failure");
                }
            }
                
        }
        catch (Exception ex) 
        { 
            //uncomment to get actual error
            //Response.Write(ex.ToString());  
        }
        finally
        {
            if (reader != null)
            {
                reader.Close();
                reader = null;
            }
            if (cmd != null)
            {
                cmd.Dispose();
                cmd = null;
            }
            if (conn != null)
            {
                conn.Dispose();
                conn.Close();
            }
            conn = null;
        }
        
        Response.Flush();
        Response.End();
    }

    string GetFormVar(string name)
    {
        return Request.Form[name].Replace("ABC-WANGOMAIL-ABC", "\0");
    }
</script>