USE [master]
GO
/****** Object:  Database [document_manager]    Script Date: 26/02/2024 14:51:50 ******/
CREATE DATABASE [document_manager]
 CONTAINMENT = NONE
 ON  PRIMARY 
( NAME = N'testingdb', FILENAME = N'C:\Program Files\Microsoft SQL Server\MSSQL15.MSSQLSERVER\MSSQL\DATA\testingdb.mdf' , SIZE = 73728KB , MAXSIZE = UNLIMITED, FILEGROWTH = 65536KB )
 LOG ON 
( NAME = N'testingdb_log', FILENAME = N'C:\Program Files\Microsoft SQL Server\MSSQL15.MSSQLSERVER\MSSQL\DATA\testingdb_log.ldf' , SIZE = 8192KB , MAXSIZE = 2048GB , FILEGROWTH = 65536KB )
 WITH CATALOG_COLLATION = DATABASE_DEFAULT
GO
ALTER DATABASE [document_manager] SET COMPATIBILITY_LEVEL = 150
GO
IF (1 = FULLTEXTSERVICEPROPERTY('IsFullTextInstalled'))
begin
EXEC [document_manager].[dbo].[sp_fulltext_database] @action = 'enable'
end
GO
ALTER DATABASE [document_manager] SET ANSI_NULL_DEFAULT OFF 
GO
ALTER DATABASE [document_manager] SET ANSI_NULLS OFF 
GO
ALTER DATABASE [document_manager] SET ANSI_PADDING OFF 
GO
ALTER DATABASE [document_manager] SET ANSI_WARNINGS OFF 
GO
ALTER DATABASE [document_manager] SET ARITHABORT OFF 
GO
ALTER DATABASE [document_manager] SET AUTO_CLOSE OFF 
GO
ALTER DATABASE [document_manager] SET AUTO_SHRINK OFF 
GO
ALTER DATABASE [document_manager] SET AUTO_UPDATE_STATISTICS ON 
GO
ALTER DATABASE [document_manager] SET CURSOR_CLOSE_ON_COMMIT OFF 
GO
ALTER DATABASE [document_manager] SET CURSOR_DEFAULT  GLOBAL 
GO
ALTER DATABASE [document_manager] SET CONCAT_NULL_YIELDS_NULL OFF 
GO
ALTER DATABASE [document_manager] SET NUMERIC_ROUNDABORT OFF 
GO
ALTER DATABASE [document_manager] SET QUOTED_IDENTIFIER OFF 
GO
ALTER DATABASE [document_manager] SET RECURSIVE_TRIGGERS OFF 
GO
ALTER DATABASE [document_manager] SET  DISABLE_BROKER 
GO
ALTER DATABASE [document_manager] SET AUTO_UPDATE_STATISTICS_ASYNC OFF 
GO
ALTER DATABASE [document_manager] SET DATE_CORRELATION_OPTIMIZATION OFF 
GO
ALTER DATABASE [document_manager] SET TRUSTWORTHY OFF 
GO
ALTER DATABASE [document_manager] SET ALLOW_SNAPSHOT_ISOLATION OFF 
GO
ALTER DATABASE [document_manager] SET PARAMETERIZATION SIMPLE 
GO
ALTER DATABASE [document_manager] SET READ_COMMITTED_SNAPSHOT OFF 
GO
ALTER DATABASE [document_manager] SET HONOR_BROKER_PRIORITY OFF 
GO
ALTER DATABASE [document_manager] SET RECOVERY SIMPLE 
GO
ALTER DATABASE [document_manager] SET  MULTI_USER 
GO
ALTER DATABASE [document_manager] SET PAGE_VERIFY CHECKSUM  
GO
ALTER DATABASE [document_manager] SET DB_CHAINING OFF 
GO
ALTER DATABASE [document_manager] SET FILESTREAM( NON_TRANSACTED_ACCESS = OFF ) 
GO
ALTER DATABASE [document_manager] SET TARGET_RECOVERY_TIME = 60 SECONDS 
GO
ALTER DATABASE [document_manager] SET DELAYED_DURABILITY = DISABLED 
GO
ALTER DATABASE [document_manager] SET ACCELERATED_DATABASE_RECOVERY = OFF  
GO
ALTER DATABASE [document_manager] SET QUERY_STORE = OFF
GO
USE [document_manager]
GO
/****** Object:  Table [dbo].[folder]    Script Date: 26/02/2024 14:51:51 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[folder](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[fk_folder] [int] NULL,
	[name] [varchar](max) NOT NULL,
	[root_folder] [bit] NOT NULL,
	[sub_level] [int] NULL,
	[created_date] [datetime] NOT NULL,
 CONSTRAINT [PK_folder] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  View [dbo].[vw_folder_hierarchy]    Script Date: 26/02/2024 14:51:51 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE VIEW [dbo].[vw_folder_hierarchy]
AS
WITH folder_tree AS (SELECT id, fk_folder, name, sub_level
                                          FROM      dbo.folder
                                          WHERE   (fk_folder IS NULL)
                                          UNION ALL
                                          SELECT f.id, f.fk_folder, f.name, f.sub_level
                                          FROM     dbo.folder AS f INNER JOIN
                                                            folder_tree AS t ON f.fk_folder = t.id)
    SELECT id, fk_folder, name, sub_level
    FROM     folder_tree AS folder_tree_1
GO
/****** Object:  Table [dbo].[affiliation]    Script Date: 26/02/2024 14:51:51 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[affiliation](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[name] [varchar](50) NULL,
 CONSTRAINT [PK_affiliation] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[revision]    Script Date: 26/02/2024 14:51:51 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[revision](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[fk_document] [int] NOT NULL,
	[document_content] [varbinary](max) NOT NULL,
	[version] [varchar](50) NOT NULL,
	[is_visible] [bit] NOT NULL,
	[created_date] [datetime] NOT NULL,
	[is_informed] [bit] NOT NULL,
	[comment] [varchar](max) NULL,
 CONSTRAINT [PK_revision] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[affiliation_document]    Script Date: 26/02/2024 14:51:51 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[affiliation_document](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[fk_document] [int] NOT NULL,
	[fk_affiliation] [int] NOT NULL,
 CONSTRAINT [PK_affiliation_table] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[document]    Script Date: 26/02/2024 14:51:51 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[document](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[fk_folder] [int] NOT NULL,
	[name] [varchar](max) NOT NULL,
	[tags] [varchar](50) NOT NULL,
	[sub_level] [int] NULL,
 CONSTRAINT [PK_document] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  View [dbo].[vw_folder_and_document]    Script Date: 26/02/2024 14:51:51 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE VIEW [dbo].[vw_folder_and_document]
AS
SELECT
	*
FROM
	(
		(
			SELECT
				'folder' AS [type],
				[folder].[id],
				[folder].[fk_folder],
				[folder].[name],
				NULL AS [tags],
				[folder].[sub_level],
				[folder].[root_folder],
				NULL AS [version],
				(
					SELECT
						max(created_date) AS [created_date]
					FROM
						[revision]
						LEFT JOIN [document] ON document.fk_folder = folder.id
				) AS [created_date],
				NULL AS [affiliation]
			FROM
				[folder]
				LEFT JOIN [document] ON document.fk_folder = folder.id
				LEFT JOIN [revision] ON revision.fk_document = document.id
		)
        UNION
		(
			SELECT
				'document' AS [type],
				[revision].[id],
				[document].[fk_folder],
				[document].[name],
				[document].[tags],
				[document].[sub_level],
				NULL AS [root_folder],
				[revision].[version],
				[revision].[created_date],
				(
					SELECT
						STRING_AGG(affiliation.name, ', ') AS [affiliation]
					FROM
						[affiliation]
						LEFT JOIN [affiliation_document] ON affiliation.id = affiliation_document.fk_affiliation
					WHERE
						fk_document = document.id
				) AS [affiliation]
            FROM
				[document]
				LEFT JOIN [folder] ON document.fk_folder = folder.id
				LEFT JOIN [revision] ON revision.fk_document = document.id
				LEFT JOIN [affiliation_document] ON affiliation_document.fk_document = document.id
				LEFT JOIN [affiliation] ON affiliation.id = affiliation_document.fk_affiliation
            WHERE
				revision.is_visible = 1
		)
	) [documentName]
GO
/****** Object:  Table [dbo].[cron_schedule]    Script Date: 26/02/2024 14:51:51 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[cron_schedule](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[task_name] [nvarchar](50) NOT NULL,
	[last_run] [datetime] NULL,
	[next_run] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
ALTER TABLE [dbo].[folder] ADD  CONSTRAINT [DF_folder_fk_folder]  DEFAULT (NULL) FOR [fk_folder]
GO
ALTER TABLE [dbo].[folder] ADD  CONSTRAINT [DF_folder_root_folder]  DEFAULT ((0)) FOR [root_folder]
GO
ALTER TABLE [dbo].[affiliation_document]  WITH CHECK ADD  CONSTRAINT [FK_affiliation_table_affiliation] FOREIGN KEY([fk_affiliation])
REFERENCES [dbo].[affiliation] ([id])
GO
ALTER TABLE [dbo].[affiliation_document] CHECK CONSTRAINT [FK_affiliation_table_affiliation]
GO
ALTER TABLE [dbo].[affiliation_document]  WITH CHECK ADD  CONSTRAINT [FK_affiliation_table_document] FOREIGN KEY([fk_document])
REFERENCES [dbo].[document] ([id])
GO
ALTER TABLE [dbo].[affiliation_document] CHECK CONSTRAINT [FK_affiliation_table_document]
GO
ALTER TABLE [dbo].[document]  WITH CHECK ADD  CONSTRAINT [FK_document_folder] FOREIGN KEY([fk_folder])
REFERENCES [dbo].[folder] ([id])
GO
ALTER TABLE [dbo].[document] CHECK CONSTRAINT [FK_document_folder]
GO
ALTER TABLE [dbo].[folder]  WITH CHECK ADD  CONSTRAINT [FK_folder_folder] FOREIGN KEY([fk_folder])
REFERENCES [dbo].[folder] ([id])
GO
ALTER TABLE [dbo].[folder] CHECK CONSTRAINT [FK_folder_folder]
GO
ALTER TABLE [dbo].[revision]  WITH CHECK ADD  CONSTRAINT [FK_revision_document] FOREIGN KEY([fk_document])
REFERENCES [dbo].[document] ([id])
GO
ALTER TABLE [dbo].[revision] CHECK CONSTRAINT [FK_revision_document]
GO
EXEC sys.sp_addextendedproperty @name=N'MS_DiagramPane1', @value=N'[0E232FF0-B466-11cf-A24F-00AA00A3EFFF, 1.00]
Begin DesignProperties = 
   Begin PaneConfigurations = 
      Begin PaneConfiguration = 0
         NumPanes = 4
         Configuration = "(H (1[40] 4[20] 2[20] 3) )"
      End
      Begin PaneConfiguration = 1
         NumPanes = 3
         Configuration = "(H (1 [50] 4 [25] 3))"
      End
      Begin PaneConfiguration = 2
         NumPanes = 3
         Configuration = "(H (1 [50] 2 [25] 3))"
      End
      Begin PaneConfiguration = 3
         NumPanes = 3
         Configuration = "(H (4 [30] 2 [40] 3))"
      End
      Begin PaneConfiguration = 4
         NumPanes = 2
         Configuration = "(H (1 [56] 3))"
      End
      Begin PaneConfiguration = 5
         NumPanes = 2
         Configuration = "(H (2 [66] 3))"
      End
      Begin PaneConfiguration = 6
         NumPanes = 2
         Configuration = "(H (4 [50] 3))"
      End
      Begin PaneConfiguration = 7
         NumPanes = 1
         Configuration = "(V (3))"
      End
      Begin PaneConfiguration = 8
         NumPanes = 3
         Configuration = "(H (1[56] 4[18] 2) )"
      End
      Begin PaneConfiguration = 9
         NumPanes = 2
         Configuration = "(H (1 [75] 4))"
      End
      Begin PaneConfiguration = 10
         NumPanes = 2
         Configuration = "(H (1[66] 2) )"
      End
      Begin PaneConfiguration = 11
         NumPanes = 2
         Configuration = "(H (4 [60] 2))"
      End
      Begin PaneConfiguration = 12
         NumPanes = 1
         Configuration = "(H (1) )"
      End
      Begin PaneConfiguration = 13
         NumPanes = 1
         Configuration = "(V (4))"
      End
      Begin PaneConfiguration = 14
         NumPanes = 1
         Configuration = "(V (2))"
      End
      ActivePaneConfig = 0
   End
   Begin DiagramPane = 
      Begin Origin = 
         Top = 0
         Left = 0
      End
      Begin Tables = 
      End
   End
   Begin SQLPane = 
   End
   Begin DataPane = 
      Begin ParameterDefaults = ""
      End
   End
   Begin CriteriaPane = 
      Begin ColumnWidths = 11
         Column = 1440
         Alias = 900
         Table = 1176
         Output = 720
         Append = 1400
         NewValue = 1170
         SortType = 1356
         SortOrder = 1416
         GroupBy = 1350
         Filter = 1356
         Or = 1350
         Or = 1350
         Or = 1350
      End
   End
End
' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'VIEW',@level1name=N'vw_folder_and_document'
GO
EXEC sys.sp_addextendedproperty @name=N'MS_DiagramPaneCount', @value=1 , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'VIEW',@level1name=N'vw_folder_and_document'
GO
EXEC sys.sp_addextendedproperty @name=N'MS_DiagramPane1', @value=N'[0E232FF0-B466-11cf-A24F-00AA00A3EFFF, 1.00]
Begin DesignProperties = 
   Begin PaneConfigurations = 
      Begin PaneConfiguration = 0
         NumPanes = 4
         Configuration = "(H (1[40] 4[20] 2[20] 3) )"
      End
      Begin PaneConfiguration = 1
         NumPanes = 3
         Configuration = "(H (1 [50] 4 [25] 3))"
      End
      Begin PaneConfiguration = 2
         NumPanes = 3
         Configuration = "(H (1 [50] 2 [25] 3))"
      End
      Begin PaneConfiguration = 3
         NumPanes = 3
         Configuration = "(H (4 [30] 2 [40] 3))"
      End
      Begin PaneConfiguration = 4
         NumPanes = 2
         Configuration = "(H (1 [56] 3))"
      End
      Begin PaneConfiguration = 5
         NumPanes = 2
         Configuration = "(H (2 [66] 3))"
      End
      Begin PaneConfiguration = 6
         NumPanes = 2
         Configuration = "(H (4 [50] 3))"
      End
      Begin PaneConfiguration = 7
         NumPanes = 1
         Configuration = "(V (3))"
      End
      Begin PaneConfiguration = 8
         NumPanes = 3
         Configuration = "(H (1[56] 4[18] 2) )"
      End
      Begin PaneConfiguration = 9
         NumPanes = 2
         Configuration = "(H (1 [75] 4))"
      End
      Begin PaneConfiguration = 10
         NumPanes = 2
         Configuration = "(H (1[66] 2) )"
      End
      Begin PaneConfiguration = 11
         NumPanes = 2
         Configuration = "(H (4 [60] 2))"
      End
      Begin PaneConfiguration = 12
         NumPanes = 1
         Configuration = "(H (1) )"
      End
      Begin PaneConfiguration = 13
         NumPanes = 1
         Configuration = "(V (4))"
      End
      Begin PaneConfiguration = 14
         NumPanes = 1
         Configuration = "(V (2))"
      End
      ActivePaneConfig = 0
   End
   Begin DiagramPane = 
      Begin Origin = 
         Top = 0
         Left = 0
      End
      Begin Tables = 
         Begin Table = "folder_tree_1"
            Begin Extent = 
               Top = 7
               Left = 48
               Bottom = 170
               Right = 242
            End
            DisplayFlags = 280
            TopColumn = 0
         End
      End
   End
   Begin SQLPane = 
   End
   Begin DataPane = 
      Begin ParameterDefaults = ""
      End
   End
   Begin CriteriaPane = 
      Begin ColumnWidths = 11
         Column = 1440
         Alias = 900
         Table = 1170
         Output = 720
         Append = 1400
         NewValue = 1170
         SortType = 1350
         SortOrder = 1410
         GroupBy = 1350
         Filter = 1350
         Or = 1350
         Or = 1350
         Or = 1350
      End
   End
End
' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'VIEW',@level1name=N'vw_folder_hierarchy'
GO
EXEC sys.sp_addextendedproperty @name=N'MS_DiagramPaneCount', @value=1 , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'VIEW',@level1name=N'vw_folder_hierarchy'
GO
USE [master]
GO
ALTER DATABASE [document_manager] SET  READ_WRITE 
GO
