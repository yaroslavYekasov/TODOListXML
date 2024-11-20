<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<!-- принимаем текущую дату как параметр -->
	<xsl:param name="currentDate" />

	<xsl:template match="/">
		<html>
			<head>
				<title>TODO nimekiri</title>
				<style>
					/* делаем таблицу аккуратной */
					table { width: 100%; border-collapse: collapse; }
					th, td { border: 1px solid black; padding: 8px; text-align: left; }
					th { background-color: #f2f2f2; }
					.expired { background-color: orange; } /* просроченные задачи выделяются */
				</style>
			</head>
			<body>
				<h2>TODO nimekiri</h2>

				<!-- выводим общее количество задач -->
				<p>
					<!-- считаем количество задач -->
					<xsl:variable name="taskCount" select="count(dim1/tasks/task)" />
					Kokku ülesandeid: <xsl:value-of select="$taskCount" />
				</p>

				<table>
					<tr>
						<!-- шапка таблицы -->
						<th>ID</th>
						<th>Kuupäev</th>
						<th>Tähtaeg</th>
						<th>Õppeaine</th>
						<th>Teave</th>
						<th>Kirjeldus</th>
					</tr>
					<!-- сортируем задачи по дедлайну -->
					<xsl:for-each select="dim1/tasks/task">
						<xsl:sort select="deadline" data-type="text" order="ascending" />
						<tr>
							<!-- если дедлайн истек, добавляем класс expired -->
							<xsl:attribute name="class">
								<xsl:if test="deadline &lt; $currentDate">expired</xsl:if>
							</xsl:attribute>
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
