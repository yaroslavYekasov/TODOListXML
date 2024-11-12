<%@ Page Language="C#" AutoEventWireup="true" CodeFile="Default.aspx.cs" Inherits="TODOListXML._Default" %>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head runat="server">
    <title>TODO List - Autod1</title>
</head>
<body>
    <form id="form1" runat="server">
        <div>
            <asp:Xml ID="XmlControl" runat="server"
                DocumentSource="~/TODO/TODO.xml"
                TransformSource="~/TODO/TODO.xslt" />
        </div>
    </form>
</body>
</html>
