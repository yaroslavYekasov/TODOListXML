<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<!-- Определение стиля XSLT для преобразования XML в HTML -->
	<xsl:template match="/">
		<!-- Создание HTML-страницы -->
		<html>
			<head>
				<title>TODO nimekiri</title>
				<style>
					/* Стили для таблицы */
					table { width: 100%; border-collapse: collapse; }
					th, td { border: 1px solid black; padding: 8px; text-align: left; }
					th { background-color: #f2f2f2; }
				</style>
			</head>
			<body>
				<!-- Заголовок таблицы -->
				<h2>TODO nimekiri</h2>
				<table>
					<!-- Шапка таблицы с названиями столбцов -->
					<tr>
						<th>ID</th>
						<th>Kuupäev</th>
						<th>Tähtaeg</th>
						<th>Õppeaine</th>
						<th>Teave</th>
						<th>Kirjeldus</th>
					</tr>
					<!-- Перебор элементов XML (каждая задача) -->
					<xsl:for-each select="dim1/tasks/task">
						<tr>
							<!-- Отображение данных каждой задачи в строке таблицы -->
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
