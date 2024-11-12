<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:template match="/">
		<html>
			<head>
				<title>TODO nimekiri</title>
				<style>
					table { width: 100%; border-collapse: collapse; }
					th, td { border: 1px solid black; padding: 8px; text-align: left; }
					th { background-color: #f2f2f2; }
				</style>
			</head>
			<body>
				<h2>TODO nimekiri</h2>
				<table>
					<tr>
						<th>ID</th>
						<th>Kuupäev</th>
						<th>Tähtaeg</th>
						<th>Õppeaine</th>
						<th>Teave</th>
						<th>Kirjeldus</th>
					</tr>
					<xsl:for-each select="dim1/tasks/task">
						<tr>
							<td>
								<xsl:value-of select="@id" />
							</td>
							<td>
								<xsl:value-of select="date" />
							</td>
							<td>
								<xsl:value-of select="deadline" />
							</td>
							<td>
								<xsl:value-of select="subject" />
							</td>
							<td>
								<xsl:value-of select="info" />
							</td>
							<td>
								<xsl:value-of select="description" />
							</td>
						</tr>
					</xsl:for-each>
				</table>
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>
