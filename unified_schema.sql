-- ============================================================
-- SITHEMR Unified Database Schema (SQL Server / T-SQL)
-- Auto-generated from tables.md
-- Usage: Execute in SQL Server Management Studio (SSMS)
-- ============================================================

-- Ensure database exists
IF NOT EXISTS (SELECT * FROM sys.databases WHERE name = N'SITHEMR')
BEGIN
    CREATE DATABASE [SITHEMR];
END
GO

USE [SITHEMR];
GO

-- tenants
IF OBJECT_ID(N'[dbo].[tenants]', N'U') IS NOT NULL
    DROP TABLE [dbo].[tenants];
GO

CREATE TABLE [dbo].[tenants] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_code] NVARCHAR(30) NOT NULL,
    [tenant_name] NVARCHAR(150) NOT NULL,
    [address] NVARCHAR(255) NULL,
    [city] NVARCHAR(100) NULL,
    [contact_number] NVARCHAR(30) NULL,
    [email] NVARCHAR(100) NULL,
    [logo_path] NVARCHAR(255) NULL,
    [philhealth_hci_code] NVARCHAR(30) NULL,
    [philhealth_accreditation_no] NVARCHAR(50) NULL,
    [accreditation_date] DATE NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_tenants] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_tenants_tenant_code' AND object_id = OBJECT_ID(N'[dbo].[tenants]'))
    ALTER TABLE [dbo].[tenants] ADD CONSTRAINT [UQ_tenants_tenant_code] UNIQUE ([tenant_code]);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_tenants_created_by' AND object_id = OBJECT_ID(N'[dbo].[tenants]'))
    CREATE NONCLUSTERED INDEX [IX_tenants_created_by] ON [dbo].[tenants]([created_by] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_tenants_updated_by' AND object_id = OBJECT_ID(N'[dbo].[tenants]'))
    CREATE NONCLUSTERED INDEX [IX_tenants_updated_by] ON [dbo].[tenants]([updated_by] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_tenants_history' AND object_id = OBJECT_ID(N'[dbo].[tenants]'))
    CREATE NONCLUSTERED INDEX [IX_tenants_history] ON [dbo].[tenants]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_tenants_is_active' AND object_id = OBJECT_ID(N'[dbo].[tenants]'))
    CREATE NONCLUSTERED INDEX [IX_tenants_is_active] ON [dbo].[tenants]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_tenants_created_date' AND object_id = OBJECT_ID(N'[dbo].[tenants]'))
    CREATE NONCLUSTERED INDEX [IX_tenants_created_date] ON [dbo].[tenants]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_tenants_created_by' AND parent_object_id = OBJECT_ID(N'[dbo].[tenants]'))
    ALTER TABLE [dbo].[tenants] ADD CONSTRAINT [FK_tenants_created_by] FOREIGN KEY ([created_by]) REFERENCES [dbo].[users]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_tenants_updated_by' AND parent_object_id = OBJECT_ID(N'[dbo].[tenants]'))
    ALTER TABLE [dbo].[tenants] ADD CONSTRAINT [FK_tenants_updated_by] FOREIGN KEY ([updated_by]) REFERENCES [dbo].[users]([id]) ON DELETE NO ACTION;
-- countries
IF OBJECT_ID(N'[dbo].[countries]', N'U') IS NOT NULL
    DROP TABLE [dbo].[countries];
GO

CREATE TABLE [dbo].[countries] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [code] NVARCHAR(10) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_countries] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_countries_code' AND object_id = OBJECT_ID(N'[dbo].[countries]'))
    ALTER TABLE [dbo].[countries] ADD CONSTRAINT [UQ_countries_code] UNIQUE ([code]);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_countries_history' AND object_id = OBJECT_ID(N'[dbo].[countries]'))
    CREATE NONCLUSTERED INDEX [IX_countries_history] ON [dbo].[countries]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_countries_is_active' AND object_id = OBJECT_ID(N'[dbo].[countries]'))
    CREATE NONCLUSTERED INDEX [IX_countries_is_active] ON [dbo].[countries]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_countries_created_date' AND object_id = OBJECT_ID(N'[dbo].[countries]'))
    CREATE NONCLUSTERED INDEX [IX_countries_created_date] ON [dbo].[countries]([created_date] ASC);
-- regions
IF OBJECT_ID(N'[dbo].[regions]', N'U') IS NOT NULL
    DROP TABLE [dbo].[regions];
GO

CREATE TABLE [dbo].[regions] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [country_id] BIGINT NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_regions] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_regions_country_id__code' AND object_id = OBJECT_ID(N'[dbo].[regions]'))
    ALTER TABLE [dbo].[regions] ADD CONSTRAINT [UQ_regions_country_id__code] UNIQUE (country_id, code);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_regions_country_id' AND object_id = OBJECT_ID(N'[dbo].[regions]'))
    CREATE NONCLUSTERED INDEX [IX_regions_country_id] ON [dbo].[regions]([country_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_regions_history' AND object_id = OBJECT_ID(N'[dbo].[regions]'))
    CREATE NONCLUSTERED INDEX [IX_regions_history] ON [dbo].[regions]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_regions_is_active' AND object_id = OBJECT_ID(N'[dbo].[regions]'))
    CREATE NONCLUSTERED INDEX [IX_regions_is_active] ON [dbo].[regions]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_regions_created_date' AND object_id = OBJECT_ID(N'[dbo].[regions]'))
    CREATE NONCLUSTERED INDEX [IX_regions_created_date] ON [dbo].[regions]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_regions_country_id' AND parent_object_id = OBJECT_ID(N'[dbo].[regions]'))
    ALTER TABLE [dbo].[regions] ADD CONSTRAINT [FK_regions_country_id] FOREIGN KEY ([country_id]) REFERENCES [dbo].[countries]([id]) ON DELETE NO ACTION;
-- provinces_cities
IF OBJECT_ID(N'[dbo].[provinces_cities]', N'U') IS NOT NULL
    DROP TABLE [dbo].[provinces_cities];
GO

CREATE TABLE [dbo].[provinces_cities] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [region_id] BIGINT NOT NULL,
    [parent_id] BIGINT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [type] NVARCHAR(20) NOT NULL DEFAULT (N'province'),
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_provinces_cities] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_provinces_cities_region_id__code' AND object_id = OBJECT_ID(N'[dbo].[provinces_cities]'))
    ALTER TABLE [dbo].[provinces_cities] ADD CONSTRAINT [UQ_provinces_cities_region_id__code] UNIQUE (region_id, code);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_provinces_cities_region_id' AND object_id = OBJECT_ID(N'[dbo].[provinces_cities]'))
    CREATE NONCLUSTERED INDEX [IX_provinces_cities_region_id] ON [dbo].[provinces_cities]([region_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_provinces_cities_parent_id' AND object_id = OBJECT_ID(N'[dbo].[provinces_cities]'))
    CREATE NONCLUSTERED INDEX [IX_provinces_cities_parent_id] ON [dbo].[provinces_cities]([parent_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_provinces_cities_history' AND object_id = OBJECT_ID(N'[dbo].[provinces_cities]'))
    CREATE NONCLUSTERED INDEX [IX_provinces_cities_history] ON [dbo].[provinces_cities]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_provinces_cities_is_active' AND object_id = OBJECT_ID(N'[dbo].[provinces_cities]'))
    CREATE NONCLUSTERED INDEX [IX_provinces_cities_is_active] ON [dbo].[provinces_cities]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_provinces_cities_created_date' AND object_id = OBJECT_ID(N'[dbo].[provinces_cities]'))
    CREATE NONCLUSTERED INDEX [IX_provinces_cities_created_date] ON [dbo].[provinces_cities]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_provinces_cities_region_id' AND parent_object_id = OBJECT_ID(N'[dbo].[provinces_cities]'))
    ALTER TABLE [dbo].[provinces_cities] ADD CONSTRAINT [FK_provinces_cities_region_id] FOREIGN KEY ([region_id]) REFERENCES [dbo].[regions]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_provinces_cities_parent_id' AND parent_object_id = OBJECT_ID(N'[dbo].[provinces_cities]'))
    ALTER TABLE [dbo].[provinces_cities] ADD CONSTRAINT [FK_provinces_cities_parent_id] FOREIGN KEY ([parent_id]) REFERENCES [dbo].[provinces_cities]([id]) ON DELETE NO ACTION;
-- barangays
IF OBJECT_ID(N'[dbo].[barangays]', N'U') IS NOT NULL
    DROP TABLE [dbo].[barangays];
GO

CREATE TABLE [dbo].[barangays] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [province_city_id] BIGINT NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_barangays] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_barangays_province_city_id__code' AND object_id = OBJECT_ID(N'[dbo].[barangays]'))
    ALTER TABLE [dbo].[barangays] ADD CONSTRAINT [UQ_barangays_province_city_id__code] UNIQUE (province_city_id, code);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_barangays_province_city_id' AND object_id = OBJECT_ID(N'[dbo].[barangays]'))
    CREATE NONCLUSTERED INDEX [IX_barangays_province_city_id] ON [dbo].[barangays]([province_city_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_barangays_history' AND object_id = OBJECT_ID(N'[dbo].[barangays]'))
    CREATE NONCLUSTERED INDEX [IX_barangays_history] ON [dbo].[barangays]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_barangays_is_active' AND object_id = OBJECT_ID(N'[dbo].[barangays]'))
    CREATE NONCLUSTERED INDEX [IX_barangays_is_active] ON [dbo].[barangays]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_barangays_created_date' AND object_id = OBJECT_ID(N'[dbo].[barangays]'))
    CREATE NONCLUSTERED INDEX [IX_barangays_created_date] ON [dbo].[barangays]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_barangays_province_city_id' AND parent_object_id = OBJECT_ID(N'[dbo].[barangays]'))
    ALTER TABLE [dbo].[barangays] ADD CONSTRAINT [FK_barangays_province_city_id] FOREIGN KEY ([province_city_id]) REFERENCES [dbo].[provinces_cities]([id]) ON DELETE NO ACTION;
-- religions
IF OBJECT_ID(N'[dbo].[religions]', N'U') IS NOT NULL
    DROP TABLE [dbo].[religions];
GO

CREATE TABLE [dbo].[religions] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_religions] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_religions_code' AND object_id = OBJECT_ID(N'[dbo].[religions]'))
    ALTER TABLE [dbo].[religions] ADD CONSTRAINT [UQ_religions_code] UNIQUE ([code]);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_religions_history' AND object_id = OBJECT_ID(N'[dbo].[religions]'))
    CREATE NONCLUSTERED INDEX [IX_religions_history] ON [dbo].[religions]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_religions_is_active' AND object_id = OBJECT_ID(N'[dbo].[religions]'))
    CREATE NONCLUSTERED INDEX [IX_religions_is_active] ON [dbo].[religions]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_religions_created_date' AND object_id = OBJECT_ID(N'[dbo].[religions]'))
    CREATE NONCLUSTERED INDEX [IX_religions_created_date] ON [dbo].[religions]([created_date] ASC);
-- genders
IF OBJECT_ID(N'[dbo].[genders]', N'U') IS NOT NULL
    DROP TABLE [dbo].[genders];
GO

CREATE TABLE [dbo].[genders] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_genders] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_genders_code' AND object_id = OBJECT_ID(N'[dbo].[genders]'))
    ALTER TABLE [dbo].[genders] ADD CONSTRAINT [UQ_genders_code] UNIQUE ([code]);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_genders_history' AND object_id = OBJECT_ID(N'[dbo].[genders]'))
    CREATE NONCLUSTERED INDEX [IX_genders_history] ON [dbo].[genders]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_genders_is_active' AND object_id = OBJECT_ID(N'[dbo].[genders]'))
    CREATE NONCLUSTERED INDEX [IX_genders_is_active] ON [dbo].[genders]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_genders_created_date' AND object_id = OBJECT_ID(N'[dbo].[genders]'))
    CREATE NONCLUSTERED INDEX [IX_genders_created_date] ON [dbo].[genders]([created_date] ASC);
-- civil_statuses
IF OBJECT_ID(N'[dbo].[civil_statuses]', N'U') IS NOT NULL
    DROP TABLE [dbo].[civil_statuses];
GO

CREATE TABLE [dbo].[civil_statuses] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_civil_statuses] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_civil_statuses_code' AND object_id = OBJECT_ID(N'[dbo].[civil_statuses]'))
    ALTER TABLE [dbo].[civil_statuses] ADD CONSTRAINT [UQ_civil_statuses_code] UNIQUE ([code]);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_civil_statuses_history' AND object_id = OBJECT_ID(N'[dbo].[civil_statuses]'))
    CREATE NONCLUSTERED INDEX [IX_civil_statuses_history] ON [dbo].[civil_statuses]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_civil_statuses_is_active' AND object_id = OBJECT_ID(N'[dbo].[civil_statuses]'))
    CREATE NONCLUSTERED INDEX [IX_civil_statuses_is_active] ON [dbo].[civil_statuses]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_civil_statuses_created_date' AND object_id = OBJECT_ID(N'[dbo].[civil_statuses]'))
    CREATE NONCLUSTERED INDEX [IX_civil_statuses_created_date] ON [dbo].[civil_statuses]([created_date] ASC);
-- nationalities
IF OBJECT_ID(N'[dbo].[nationalities]', N'U') IS NOT NULL
    DROP TABLE [dbo].[nationalities];
GO

CREATE TABLE [dbo].[nationalities] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_nationalities] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_nationalities_code' AND object_id = OBJECT_ID(N'[dbo].[nationalities]'))
    ALTER TABLE [dbo].[nationalities] ADD CONSTRAINT [UQ_nationalities_code] UNIQUE ([code]);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_nationalities_history' AND object_id = OBJECT_ID(N'[dbo].[nationalities]'))
    CREATE NONCLUSTERED INDEX [IX_nationalities_history] ON [dbo].[nationalities]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_nationalities_is_active' AND object_id = OBJECT_ID(N'[dbo].[nationalities]'))
    CREATE NONCLUSTERED INDEX [IX_nationalities_is_active] ON [dbo].[nationalities]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_nationalities_created_date' AND object_id = OBJECT_ID(N'[dbo].[nationalities]'))
    CREATE NONCLUSTERED INDEX [IX_nationalities_created_date] ON [dbo].[nationalities]([created_date] ASC);
-- occupations
IF OBJECT_ID(N'[dbo].[occupations]', N'U') IS NOT NULL
    DROP TABLE [dbo].[occupations];
GO

CREATE TABLE [dbo].[occupations] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_occupations] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_occupations_code' AND object_id = OBJECT_ID(N'[dbo].[occupations]'))
    ALTER TABLE [dbo].[occupations] ADD CONSTRAINT [UQ_occupations_code] UNIQUE ([code]);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_occupations_history' AND object_id = OBJECT_ID(N'[dbo].[occupations]'))
    CREATE NONCLUSTERED INDEX [IX_occupations_history] ON [dbo].[occupations]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_occupations_is_active' AND object_id = OBJECT_ID(N'[dbo].[occupations]'))
    CREATE NONCLUSTERED INDEX [IX_occupations_is_active] ON [dbo].[occupations]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_occupations_created_date' AND object_id = OBJECT_ID(N'[dbo].[occupations]'))
    CREATE NONCLUSTERED INDEX [IX_occupations_created_date] ON [dbo].[occupations]([created_date] ASC);
-- blood_types
IF OBJECT_ID(N'[dbo].[blood_types]', N'U') IS NOT NULL
    DROP TABLE [dbo].[blood_types];
GO

CREATE TABLE [dbo].[blood_types] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [code] NVARCHAR(10) NOT NULL,
    [name] NVARCHAR(50) NOT NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_blood_types] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_blood_types_code' AND object_id = OBJECT_ID(N'[dbo].[blood_types]'))
    ALTER TABLE [dbo].[blood_types] ADD CONSTRAINT [UQ_blood_types_code] UNIQUE ([code]);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_blood_types_history' AND object_id = OBJECT_ID(N'[dbo].[blood_types]'))
    CREATE NONCLUSTERED INDEX [IX_blood_types_history] ON [dbo].[blood_types]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_blood_types_is_active' AND object_id = OBJECT_ID(N'[dbo].[blood_types]'))
    CREATE NONCLUSTERED INDEX [IX_blood_types_is_active] ON [dbo].[blood_types]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_blood_types_created_date' AND object_id = OBJECT_ID(N'[dbo].[blood_types]'))
    CREATE NONCLUSTERED INDEX [IX_blood_types_created_date] ON [dbo].[blood_types]([created_date] ASC);
-- relationships
IF OBJECT_ID(N'[dbo].[relationships]', N'U') IS NOT NULL
    DROP TABLE [dbo].[relationships];
GO

CREATE TABLE [dbo].[relationships] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_relationships] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_relationships_code' AND object_id = OBJECT_ID(N'[dbo].[relationships]'))
    ALTER TABLE [dbo].[relationships] ADD CONSTRAINT [UQ_relationships_code] UNIQUE ([code]);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_relationships_history' AND object_id = OBJECT_ID(N'[dbo].[relationships]'))
    CREATE NONCLUSTERED INDEX [IX_relationships_history] ON [dbo].[relationships]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_relationships_is_active' AND object_id = OBJECT_ID(N'[dbo].[relationships]'))
    CREATE NONCLUSTERED INDEX [IX_relationships_is_active] ON [dbo].[relationships]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_relationships_created_date' AND object_id = OBJECT_ID(N'[dbo].[relationships]'))
    CREATE NONCLUSTERED INDEX [IX_relationships_created_date] ON [dbo].[relationships]([created_date] ASC);
-- departments
IF OBJECT_ID(N'[dbo].[departments]', N'U') IS NOT NULL
    DROP TABLE [dbo].[departments];
GO

CREATE TABLE [dbo].[departments] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [dept_code] NVARCHAR(20) NOT NULL,
    [dept_name] NVARCHAR(100) NOT NULL,
    [description] NVARCHAR(255) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_departments] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_departments_tenant_id__dept_code' AND object_id = OBJECT_ID(N'[dbo].[departments]'))
    ALTER TABLE [dbo].[departments] ADD CONSTRAINT [UQ_departments_tenant_id__dept_code] UNIQUE (tenant_id, dept_code);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_departments_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[departments]'))
    CREATE NONCLUSTERED INDEX [IX_departments_tenant_id] ON [dbo].[departments]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_departments_history' AND object_id = OBJECT_ID(N'[dbo].[departments]'))
    CREATE NONCLUSTERED INDEX [IX_departments_history] ON [dbo].[departments]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_departments_is_active' AND object_id = OBJECT_ID(N'[dbo].[departments]'))
    CREATE NONCLUSTERED INDEX [IX_departments_is_active] ON [dbo].[departments]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_departments_created_date' AND object_id = OBJECT_ID(N'[dbo].[departments]'))
    CREATE NONCLUSTERED INDEX [IX_departments_created_date] ON [dbo].[departments]([created_date] ASC);
-- users
IF OBJECT_ID(N'[dbo].[users]', N'U') IS NOT NULL
    DROP TABLE [dbo].[users];
GO

CREATE TABLE [dbo].[users] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NULL,
    [username] NVARCHAR(60) NOT NULL,
    [full_name] NVARCHAR(150) NOT NULL,
    [email] NVARCHAR(150) NOT NULL,
    [password] NVARCHAR(255) NOT NULL,
    [role] NVARCHAR(30) NOT NULL DEFAULT (N'tenant_admin'),
    [health_care_professional_id] BIGINT NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_users] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_users_email' AND object_id = OBJECT_ID(N'[dbo].[users]'))
    ALTER TABLE [dbo].[users] ADD CONSTRAINT [UQ_users_email] UNIQUE ([email]);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_users_tenant_id__username' AND object_id = OBJECT_ID(N'[dbo].[users]'))
    ALTER TABLE [dbo].[users] ADD CONSTRAINT [UQ_users_tenant_id__username] UNIQUE (tenant_id, username);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_users_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[users]'))
    CREATE NONCLUSTERED INDEX [IX_users_tenant_id] ON [dbo].[users]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_users_history' AND object_id = OBJECT_ID(N'[dbo].[users]'))
    CREATE NONCLUSTERED INDEX [IX_users_history] ON [dbo].[users]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_users_is_active' AND object_id = OBJECT_ID(N'[dbo].[users]'))
    CREATE NONCLUSTERED INDEX [IX_users_is_active] ON [dbo].[users]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_users_created_date' AND object_id = OBJECT_ID(N'[dbo].[users]'))
    CREATE NONCLUSTERED INDEX [IX_users_created_date] ON [dbo].[users]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_users_tenant_id' AND parent_object_id = OBJECT_ID(N'[dbo].[users]'))
    ALTER TABLE [dbo].[users] ADD CONSTRAINT [FK_users_tenant_id] FOREIGN KEY ([tenant_id]) REFERENCES [dbo].[tenants]([id]) ON DELETE NO ACTION;
-- rvs_codes
IF OBJECT_ID(N'[dbo].[rvs_codes]', N'U') IS NOT NULL
    DROP TABLE [dbo].[rvs_codes];
GO

CREATE TABLE [dbo].[rvs_codes] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [code] NVARCHAR(10) NOT NULL,
    [name] NVARCHAR(255) NOT NULL,
    [type] NVARCHAR(50) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_rvs_codes] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_rvs_codes_code' AND object_id = OBJECT_ID(N'[dbo].[rvs_codes]'))
    ALTER TABLE [dbo].[rvs_codes] ADD CONSTRAINT [UQ_rvs_codes_code] UNIQUE ([code]);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_rvs_codes_history' AND object_id = OBJECT_ID(N'[dbo].[rvs_codes]'))
    CREATE NONCLUSTERED INDEX [IX_rvs_codes_history] ON [dbo].[rvs_codes]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_rvs_codes_is_active' AND object_id = OBJECT_ID(N'[dbo].[rvs_codes]'))
    CREATE NONCLUSTERED INDEX [IX_rvs_codes_is_active] ON [dbo].[rvs_codes]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_rvs_codes_created_date' AND object_id = OBJECT_ID(N'[dbo].[rvs_codes]'))
    CREATE NONCLUSTERED INDEX [IX_rvs_codes_created_date] ON [dbo].[rvs_codes]([created_date] ASC);
-- icd_10_codes
IF OBJECT_ID(N'[dbo].[icd_10_codes]', N'U') IS NOT NULL
    DROP TABLE [dbo].[icd_10_codes];
GO

CREATE TABLE [dbo].[icd_10_codes] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(500) NOT NULL,
    [category] NVARCHAR(100) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_icd_10_codes] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_icd_10_codes_code' AND object_id = OBJECT_ID(N'[dbo].[icd_10_codes]'))
    ALTER TABLE [dbo].[icd_10_codes] ADD CONSTRAINT [UQ_icd_10_codes_code] UNIQUE ([code]);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_icd_10_codes_history' AND object_id = OBJECT_ID(N'[dbo].[icd_10_codes]'))
    CREATE NONCLUSTERED INDEX [IX_icd_10_codes_history] ON [dbo].[icd_10_codes]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_icd_10_codes_is_active' AND object_id = OBJECT_ID(N'[dbo].[icd_10_codes]'))
    CREATE NONCLUSTERED INDEX [IX_icd_10_codes_is_active] ON [dbo].[icd_10_codes]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_icd_10_codes_created_date' AND object_id = OBJECT_ID(N'[dbo].[icd_10_codes]'))
    CREATE NONCLUSTERED INDEX [IX_icd_10_codes_created_date] ON [dbo].[icd_10_codes]([created_date] ASC);
-- health_care_professionals
IF OBJECT_ID(N'[dbo].[health_care_professionals]', N'U') IS NOT NULL
    DROP TABLE [dbo].[health_care_professionals];
GO

CREATE TABLE [dbo].[health_care_professionals] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [professional_code] NVARCHAR(30) NOT NULL,
    [professional_type] NVARCHAR(50) NOT NULL DEFAULT (N'Doctor'),
    [full_name] NVARCHAR(150) NOT NULL,
    [last_name] NVARCHAR(50) NULL,
    [first_name] NVARCHAR(50) NULL,
    [middle_name] NVARCHAR(50) NULL,
    [suffix] NVARCHAR(10) NULL,
    [date_of_birth] DATE NULL,
    [tin] NVARCHAR(30) NULL,
    [specialization] NVARCHAR(100) NULL,
    [prc_license] NVARCHAR(50) NULL,
    [philhealth_accreditation] NVARCHAR(50) NULL,
    [department_id] BIGINT NULL,
    [contact_number] NVARCHAR(30) NULL,
    [email] NVARCHAR(100) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_health_care_professionals] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_health_care_professionals_tenant_id__professional_code__history' AND object_id = OBJECT_ID(N'[dbo].[health_care_professionals]'))
    ALTER TABLE [dbo].[health_care_professionals] ADD CONSTRAINT [UQ_health_care_professionals_tenant_id__professional_code__history] UNIQUE (tenant_id, professional_code, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_health_care_professionals_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[health_care_professionals]'))
    CREATE NONCLUSTERED INDEX [IX_health_care_professionals_tenant_id] ON [dbo].[health_care_professionals]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_health_care_professionals_department_id' AND object_id = OBJECT_ID(N'[dbo].[health_care_professionals]'))
    CREATE NONCLUSTERED INDEX [IX_health_care_professionals_department_id] ON [dbo].[health_care_professionals]([department_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_health_care_professionals_history' AND object_id = OBJECT_ID(N'[dbo].[health_care_professionals]'))
    CREATE NONCLUSTERED INDEX [IX_health_care_professionals_history] ON [dbo].[health_care_professionals]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_health_care_professionals_is_active' AND object_id = OBJECT_ID(N'[dbo].[health_care_professionals]'))
    CREATE NONCLUSTERED INDEX [IX_health_care_professionals_is_active] ON [dbo].[health_care_professionals]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_health_care_professionals_created_date' AND object_id = OBJECT_ID(N'[dbo].[health_care_professionals]'))
    CREATE NONCLUSTERED INDEX [IX_health_care_professionals_created_date] ON [dbo].[health_care_professionals]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_health_care_professionals_department_id' AND parent_object_id = OBJECT_ID(N'[dbo].[health_care_professionals]'))
    ALTER TABLE [dbo].[health_care_professionals] ADD CONSTRAINT [FK_health_care_professionals_department_id] FOREIGN KEY ([department_id]) REFERENCES [dbo].[departments]([id]) ON DELETE NO ACTION;
-- philhealth_members
IF OBJECT_ID(N'[dbo].[philhealth_members]', N'U') IS NOT NULL
    DROP TABLE [dbo].[philhealth_members];
GO

CREATE TABLE [dbo].[philhealth_members] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [pin] NVARCHAR(30) NOT NULL,
    [last_name] NVARCHAR(60) NOT NULL,
    [first_name] NVARCHAR(60) NOT NULL,
    [middle_name] NVARCHAR(60) NULL,
    [suffix] NVARCHAR(20) NULL,
    [date_of_birth] DATE NULL,
    [gender] NVARCHAR(10) NULL,
    [membership_type] NVARCHAR(50) NULL,
    [contact_number] NVARCHAR(30) NULL,
    [email] NVARCHAR(100) NULL,
    [mailing_address] NVARCHAR(255) NULL,
    [employer_name] NVARCHAR(255) NULL,
    [employer_address] NVARCHAR(255) NULL,
    [employer_contact] NVARCHAR(30) NULL,
    [employer_signature_date] DATETIME2 NULL,
    [zip_code] NVARCHAR(10) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_philhealth_members] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_philhealth_members_tenant_id__pin' AND object_id = OBJECT_ID(N'[dbo].[philhealth_members]'))
    ALTER TABLE [dbo].[philhealth_members] ADD CONSTRAINT [UQ_philhealth_members_tenant_id__pin] UNIQUE (tenant_id, pin);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_philhealth_members_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[philhealth_members]'))
    CREATE NONCLUSTERED INDEX [IX_philhealth_members_tenant_id] ON [dbo].[philhealth_members]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_philhealth_members_history' AND object_id = OBJECT_ID(N'[dbo].[philhealth_members]'))
    CREATE NONCLUSTERED INDEX [IX_philhealth_members_history] ON [dbo].[philhealth_members]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_philhealth_members_is_active' AND object_id = OBJECT_ID(N'[dbo].[philhealth_members]'))
    CREATE NONCLUSTERED INDEX [IX_philhealth_members_is_active] ON [dbo].[philhealth_members]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_philhealth_members_created_date' AND object_id = OBJECT_ID(N'[dbo].[philhealth_members]'))
    CREATE NONCLUSTERED INDEX [IX_philhealth_members_created_date] ON [dbo].[philhealth_members]([created_date] ASC);
-- eecl_items
IF OBJECT_ID(N'[dbo].[eecl_items]', N'U') IS NOT NULL
    DROP TABLE [dbo].[eecl_items];
GO

CREATE TABLE [dbo].[eecl_items] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [item_code] NVARCHAR(30) NOT NULL,
    [category] NVARCHAR(100) NOT NULL,
    [subcategory] NVARCHAR(100) NULL,
    [item_name] NVARCHAR(300) NOT NULL,
    [description] NVARCHAR(MAX) NULL,
    [unit] NVARCHAR(50) NULL,
    [fixed_fee] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_eecl_items] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_eecl_items_item_code' AND object_id = OBJECT_ID(N'[dbo].[eecl_items]'))
    ALTER TABLE [dbo].[eecl_items] ADD CONSTRAINT [UQ_eecl_items_item_code] UNIQUE ([item_code]);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_eecl_items_category' AND object_id = OBJECT_ID(N'[dbo].[eecl_items]'))
    CREATE NONCLUSTERED INDEX [IX_eecl_items_category] ON [dbo].[eecl_items]([category] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_eecl_items_history' AND object_id = OBJECT_ID(N'[dbo].[eecl_items]'))
    CREATE NONCLUSTERED INDEX [IX_eecl_items_history] ON [dbo].[eecl_items]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_eecl_items_is_active' AND object_id = OBJECT_ID(N'[dbo].[eecl_items]'))
    CREATE NONCLUSTERED INDEX [IX_eecl_items_is_active] ON [dbo].[eecl_items]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_eecl_items_created_date' AND object_id = OBJECT_ID(N'[dbo].[eecl_items]'))
    CREATE NONCLUSTERED INDEX [IX_eecl_items_created_date] ON [dbo].[eecl_items]([created_date] ASC);
-- philhealth_case_rates
IF OBJECT_ID(N'[dbo].[philhealth_case_rates]', N'U') IS NOT NULL
    DROP TABLE [dbo].[philhealth_case_rates];
GO

CREATE TABLE [dbo].[philhealth_case_rates] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [rvs_code_id] BIGINT NULL,
    [icd10_code_id] BIGINT NULL,
    [rvs_code] NVARCHAR(10) NULL,
    [icd10_code] NVARCHAR(10) NULL,
    [description] NVARCHAR(255) NOT NULL,
    [hf_fee] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [pf_fee] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [total_rate] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_philhealth_case_rates] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_philhealth_case_rates_history' AND object_id = OBJECT_ID(N'[dbo].[philhealth_case_rates]'))
    CREATE NONCLUSTERED INDEX [IX_philhealth_case_rates_history] ON [dbo].[philhealth_case_rates]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_philhealth_case_rates_is_active' AND object_id = OBJECT_ID(N'[dbo].[philhealth_case_rates]'))
    CREATE NONCLUSTERED INDEX [IX_philhealth_case_rates_is_active] ON [dbo].[philhealth_case_rates]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_philhealth_case_rates_created_date' AND object_id = OBJECT_ID(N'[dbo].[philhealth_case_rates]'))
    CREATE NONCLUSTERED INDEX [IX_philhealth_case_rates_created_date] ON [dbo].[philhealth_case_rates]([created_date] ASC);
-- philhealth_z_benefits
IF OBJECT_ID(N'[dbo].[philhealth_z_benefits]', N'U') IS NOT NULL
    DROP TABLE [dbo].[philhealth_z_benefits];
GO

CREATE TABLE [dbo].[philhealth_z_benefits] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [philhealth_claim_id] BIGINT NOT NULL,
    [z_benefit_code] NVARCHAR(20) NOT NULL,
    [z_benefit_name] NVARCHAR(255) NULL,
    [pre_authorization_code] NVARCHAR(50) NULL,
    [tranche_number] TINYINT NOT NULL DEFAULT (1),
    [tranche_amount] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [status] NVARCHAR(30) NOT NULL DEFAULT (N'Pending'),
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_philhealth_z_benefits] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_philhealth_z_benefits_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[philhealth_z_benefits]'))
    CREATE NONCLUSTERED INDEX [IX_philhealth_z_benefits_tenant_id] ON [dbo].[philhealth_z_benefits]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_philhealth_z_benefits_philhealth_claim_id' AND object_id = OBJECT_ID(N'[dbo].[philhealth_z_benefits]'))
    CREATE NONCLUSTERED INDEX [IX_philhealth_z_benefits_philhealth_claim_id] ON [dbo].[philhealth_z_benefits]([philhealth_claim_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_philhealth_z_benefits_history' AND object_id = OBJECT_ID(N'[dbo].[philhealth_z_benefits]'))
    CREATE NONCLUSTERED INDEX [IX_philhealth_z_benefits_history] ON [dbo].[philhealth_z_benefits]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_philhealth_z_benefits_is_active' AND object_id = OBJECT_ID(N'[dbo].[philhealth_z_benefits]'))
    CREATE NONCLUSTERED INDEX [IX_philhealth_z_benefits_is_active] ON [dbo].[philhealth_z_benefits]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_philhealth_z_benefits_created_date' AND object_id = OBJECT_ID(N'[dbo].[philhealth_z_benefits]'))
    CREATE NONCLUSTERED INDEX [IX_philhealth_z_benefits_created_date] ON [dbo].[philhealth_z_benefits]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_philhealth_z_benefits_status' AND object_id = OBJECT_ID(N'[dbo].[philhealth_z_benefits]'))
    CREATE NONCLUSTERED INDEX [IX_philhealth_z_benefits_status] ON [dbo].[philhealth_z_benefits]([status] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_philhealth_z_benefits_philhealth_claim_id' AND parent_object_id = OBJECT_ID(N'[dbo].[philhealth_z_benefits]'))
    ALTER TABLE [dbo].[philhealth_z_benefits] ADD CONSTRAINT [FK_philhealth_z_benefits_philhealth_claim_id] FOREIGN KEY ([philhealth_claim_id]) REFERENCES [dbo].[philhealth_claims]([id]) ON DELETE NO ACTION;
-- patients
IF OBJECT_ID(N'[dbo].[patients]', N'U') IS NOT NULL
    DROP TABLE [dbo].[patients];
GO

CREATE TABLE [dbo].[patients] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [patient_code] NVARCHAR(20) NOT NULL,
    [philhealth_member_id] BIGINT NULL,
    [philhealth_pin] NVARCHAR(30) NULL,
    [is_philhealth_member] TINYINT NOT NULL DEFAULT (0),
    [last_name] NVARCHAR(60) NOT NULL,
    [first_name] NVARCHAR(60) NOT NULL,
    [middle_name] NVARCHAR(60) NULL,
    [maiden_name] NVARCHAR(60) NULL,
    [suffix] NVARCHAR(20) NULL,
    [date_of_birth] DATE NULL,
    [place_of_birth] NVARCHAR(255) NULL,
    [gender_id] BIGINT NULL,
    [civil_status_id] BIGINT NULL,
    [religion_id] BIGINT NULL,
    [nationality_id] BIGINT NULL,
    [occupation_id] BIGINT NULL,
    [blood_type_id] BIGINT NULL,
    [preferred_language] NVARCHAR(60) NULL,
    [national_id_no] NVARCHAR(30) NULL,
    [passport_no] NVARCHAR(30) NULL,
    [contact_no] NVARCHAR(30) NULL,
    [email] NVARCHAR(100) NULL,
    [address_street] NVARCHAR(255) NULL,
    [barangay_id] BIGINT NULL,
    [city_id] BIGINT NULL,
    [province_id] BIGINT NULL,
    [region_id] BIGINT NULL,
    [country_id] BIGINT NULL,
    [zip_code] NVARCHAR(10) NULL,
    [father_name] NVARCHAR(120) NULL,
    [mother_maiden_name] NVARCHAR(120) NULL,
    [spouse_name] NVARCHAR(120) NULL,
    [multiple_birth_indicator] TINYINT NOT NULL DEFAULT (0),
    [is_deceased] TINYINT NOT NULL DEFAULT (0),
    [date_of_death] DATE NULL,
    [primary_care_provider_id] BIGINT NULL,
    [emergency_contact_name] NVARCHAR(120) NULL,
    [emergency_contact_no] NVARCHAR(30) NULL,
    [emergency_relationship] NVARCHAR(60) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_patients] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_patients_patient_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[patients]'))
    ALTER TABLE [dbo].[patients] ADD CONSTRAINT [UQ_patients_patient_code__tenant_id__history] UNIQUE (patient_code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_patients_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[patients]'))
    CREATE NONCLUSTERED INDEX [IX_patients_tenant_id] ON [dbo].[patients]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_patients_philhealth_member_id' AND object_id = OBJECT_ID(N'[dbo].[patients]'))
    CREATE NONCLUSTERED INDEX [IX_patients_philhealth_member_id] ON [dbo].[patients]([philhealth_member_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_patients_gender_id' AND object_id = OBJECT_ID(N'[dbo].[patients]'))
    CREATE NONCLUSTERED INDEX [IX_patients_gender_id] ON [dbo].[patients]([gender_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_patients_civil_status_id' AND object_id = OBJECT_ID(N'[dbo].[patients]'))
    CREATE NONCLUSTERED INDEX [IX_patients_civil_status_id] ON [dbo].[patients]([civil_status_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_patients_religion_id' AND object_id = OBJECT_ID(N'[dbo].[patients]'))
    CREATE NONCLUSTERED INDEX [IX_patients_religion_id] ON [dbo].[patients]([religion_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_patients_nationality_id' AND object_id = OBJECT_ID(N'[dbo].[patients]'))
    CREATE NONCLUSTERED INDEX [IX_patients_nationality_id] ON [dbo].[patients]([nationality_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_patients_occupation_id' AND object_id = OBJECT_ID(N'[dbo].[patients]'))
    CREATE NONCLUSTERED INDEX [IX_patients_occupation_id] ON [dbo].[patients]([occupation_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_patients_blood_type_id' AND object_id = OBJECT_ID(N'[dbo].[patients]'))
    CREATE NONCLUSTERED INDEX [IX_patients_blood_type_id] ON [dbo].[patients]([blood_type_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_patients_barangay_id' AND object_id = OBJECT_ID(N'[dbo].[patients]'))
    CREATE NONCLUSTERED INDEX [IX_patients_barangay_id] ON [dbo].[patients]([barangay_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_patients_city_id' AND object_id = OBJECT_ID(N'[dbo].[patients]'))
    CREATE NONCLUSTERED INDEX [IX_patients_city_id] ON [dbo].[patients]([city_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_patients_province_id' AND object_id = OBJECT_ID(N'[dbo].[patients]'))
    CREATE NONCLUSTERED INDEX [IX_patients_province_id] ON [dbo].[patients]([province_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_patients_region_id' AND object_id = OBJECT_ID(N'[dbo].[patients]'))
    CREATE NONCLUSTERED INDEX [IX_patients_region_id] ON [dbo].[patients]([region_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_patients_country_id' AND object_id = OBJECT_ID(N'[dbo].[patients]'))
    CREATE NONCLUSTERED INDEX [IX_patients_country_id] ON [dbo].[patients]([country_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_patients_primary_care_provider_id' AND object_id = OBJECT_ID(N'[dbo].[patients]'))
    CREATE NONCLUSTERED INDEX [IX_patients_primary_care_provider_id] ON [dbo].[patients]([primary_care_provider_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_patients_history' AND object_id = OBJECT_ID(N'[dbo].[patients]'))
    CREATE NONCLUSTERED INDEX [IX_patients_history] ON [dbo].[patients]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_patients_is_active' AND object_id = OBJECT_ID(N'[dbo].[patients]'))
    CREATE NONCLUSTERED INDEX [IX_patients_is_active] ON [dbo].[patients]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_patients_created_date' AND object_id = OBJECT_ID(N'[dbo].[patients]'))
    CREATE NONCLUSTERED INDEX [IX_patients_created_date] ON [dbo].[patients]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_patients_philhealth_member_id' AND parent_object_id = OBJECT_ID(N'[dbo].[patients]'))
    ALTER TABLE [dbo].[patients] ADD CONSTRAINT [FK_patients_philhealth_member_id] FOREIGN KEY ([philhealth_member_id]) REFERENCES [dbo].[philhealth_members]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_patients_gender_id' AND parent_object_id = OBJECT_ID(N'[dbo].[patients]'))
    ALTER TABLE [dbo].[patients] ADD CONSTRAINT [FK_patients_gender_id] FOREIGN KEY ([gender_id]) REFERENCES [dbo].[genders]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_patients_civil_status_id' AND parent_object_id = OBJECT_ID(N'[dbo].[patients]'))
    ALTER TABLE [dbo].[patients] ADD CONSTRAINT [FK_patients_civil_status_id] FOREIGN KEY ([civil_status_id]) REFERENCES [dbo].[civil_statuses]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_patients_religion_id' AND parent_object_id = OBJECT_ID(N'[dbo].[patients]'))
    ALTER TABLE [dbo].[patients] ADD CONSTRAINT [FK_patients_religion_id] FOREIGN KEY ([religion_id]) REFERENCES [dbo].[religions]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_patients_nationality_id' AND parent_object_id = OBJECT_ID(N'[dbo].[patients]'))
    ALTER TABLE [dbo].[patients] ADD CONSTRAINT [FK_patients_nationality_id] FOREIGN KEY ([nationality_id]) REFERENCES [dbo].[nationalities]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_patients_occupation_id' AND parent_object_id = OBJECT_ID(N'[dbo].[patients]'))
    ALTER TABLE [dbo].[patients] ADD CONSTRAINT [FK_patients_occupation_id] FOREIGN KEY ([occupation_id]) REFERENCES [dbo].[occupations]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_patients_blood_type_id' AND parent_object_id = OBJECT_ID(N'[dbo].[patients]'))
    ALTER TABLE [dbo].[patients] ADD CONSTRAINT [FK_patients_blood_type_id] FOREIGN KEY ([blood_type_id]) REFERENCES [dbo].[blood_types]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_patients_barangay_id' AND parent_object_id = OBJECT_ID(N'[dbo].[patients]'))
    ALTER TABLE [dbo].[patients] ADD CONSTRAINT [FK_patients_barangay_id] FOREIGN KEY ([barangay_id]) REFERENCES [dbo].[barangays]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_patients_city_id' AND parent_object_id = OBJECT_ID(N'[dbo].[patients]'))
    ALTER TABLE [dbo].[patients] ADD CONSTRAINT [FK_patients_city_id] FOREIGN KEY ([city_id]) REFERENCES [dbo].[provinces_cities]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_patients_province_id' AND parent_object_id = OBJECT_ID(N'[dbo].[patients]'))
    ALTER TABLE [dbo].[patients] ADD CONSTRAINT [FK_patients_province_id] FOREIGN KEY ([province_id]) REFERENCES [dbo].[provinces_cities]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_patients_region_id' AND parent_object_id = OBJECT_ID(N'[dbo].[patients]'))
    ALTER TABLE [dbo].[patients] ADD CONSTRAINT [FK_patients_region_id] FOREIGN KEY ([region_id]) REFERENCES [dbo].[regions]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_patients_country_id' AND parent_object_id = OBJECT_ID(N'[dbo].[patients]'))
    ALTER TABLE [dbo].[patients] ADD CONSTRAINT [FK_patients_country_id] FOREIGN KEY ([country_id]) REFERENCES [dbo].[countries]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_patients_primary_care_provider_id' AND parent_object_id = OBJECT_ID(N'[dbo].[patients]'))
    ALTER TABLE [dbo].[patients] ADD CONSTRAINT [FK_patients_primary_care_provider_id] FOREIGN KEY ([primary_care_provider_id]) REFERENCES [dbo].[health_care_professionals]([id]) ON DELETE NO ACTION;
-- hospitals
IF OBJECT_ID(N'[dbo].[hospitals]', N'U') IS NOT NULL
    DROP TABLE [dbo].[hospitals];
GO

CREATE TABLE [dbo].[hospitals] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [code] NVARCHAR(30) NOT NULL,
    [name] NVARCHAR(200) NOT NULL,
    [address] NVARCHAR(255) NULL,
    [contact_number] NVARCHAR(30) NULL,
    [hospital_type] NVARCHAR(30) NOT NULL DEFAULT (N'Primary'),
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_hospitals] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_hospitals_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[hospitals]'))
    ALTER TABLE [dbo].[hospitals] ADD CONSTRAINT [UQ_hospitals_code__tenant_id__history] UNIQUE (code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_hospitals_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[hospitals]'))
    CREATE NONCLUSTERED INDEX [IX_hospitals_tenant_id] ON [dbo].[hospitals]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_hospitals_history' AND object_id = OBJECT_ID(N'[dbo].[hospitals]'))
    CREATE NONCLUSTERED INDEX [IX_hospitals_history] ON [dbo].[hospitals]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_hospitals_is_active' AND object_id = OBJECT_ID(N'[dbo].[hospitals]'))
    CREATE NONCLUSTERED INDEX [IX_hospitals_is_active] ON [dbo].[hospitals]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_hospitals_created_date' AND object_id = OBJECT_ID(N'[dbo].[hospitals]'))
    CREATE NONCLUSTERED INDEX [IX_hospitals_created_date] ON [dbo].[hospitals]([created_date] ASC);
-- lab_sections
IF OBJECT_ID(N'[dbo].[lab_sections]', N'U') IS NOT NULL
    DROP TABLE [dbo].[lab_sections];
GO

CREATE TABLE [dbo].[lab_sections] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [description] NVARCHAR(255) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_lab_sections] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_lab_sections_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[lab_sections]'))
    ALTER TABLE [dbo].[lab_sections] ADD CONSTRAINT [UQ_lab_sections_code__tenant_id__history] UNIQUE (code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_sections_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[lab_sections]'))
    CREATE NONCLUSTERED INDEX [IX_lab_sections_tenant_id] ON [dbo].[lab_sections]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_sections_history' AND object_id = OBJECT_ID(N'[dbo].[lab_sections]'))
    CREATE NONCLUSTERED INDEX [IX_lab_sections_history] ON [dbo].[lab_sections]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_sections_is_active' AND object_id = OBJECT_ID(N'[dbo].[lab_sections]'))
    CREATE NONCLUSTERED INDEX [IX_lab_sections_is_active] ON [dbo].[lab_sections]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_sections_created_date' AND object_id = OBJECT_ID(N'[dbo].[lab_sections]'))
    CREATE NONCLUSTERED INDEX [IX_lab_sections_created_date] ON [dbo].[lab_sections]([created_date] ASC);
-- lab_units
IF OBJECT_ID(N'[dbo].[lab_units]', N'U') IS NOT NULL
    DROP TABLE [dbo].[lab_units];
GO

CREATE TABLE [dbo].[lab_units] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [category] NVARCHAR(50) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_lab_units] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_lab_units_code' AND object_id = OBJECT_ID(N'[dbo].[lab_units]'))
    ALTER TABLE [dbo].[lab_units] ADD CONSTRAINT [UQ_lab_units_code] UNIQUE ([code]);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_units_history' AND object_id = OBJECT_ID(N'[dbo].[lab_units]'))
    CREATE NONCLUSTERED INDEX [IX_lab_units_history] ON [dbo].[lab_units]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_units_is_active' AND object_id = OBJECT_ID(N'[dbo].[lab_units]'))
    CREATE NONCLUSTERED INDEX [IX_lab_units_is_active] ON [dbo].[lab_units]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_units_created_date' AND object_id = OBJECT_ID(N'[dbo].[lab_units]'))
    CREATE NONCLUSTERED INDEX [IX_lab_units_created_date] ON [dbo].[lab_units]([created_date] ASC);
-- specimen_types
IF OBJECT_ID(N'[dbo].[specimen_types]', N'U') IS NOT NULL
    DROP TABLE [dbo].[specimen_types];
GO

CREATE TABLE [dbo].[specimen_types] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [description] NVARCHAR(255) NULL,
    [container] NVARCHAR(100) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_specimen_types] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_specimen_types_code' AND object_id = OBJECT_ID(N'[dbo].[specimen_types]'))
    ALTER TABLE [dbo].[specimen_types] ADD CONSTRAINT [UQ_specimen_types_code] UNIQUE ([code]);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_specimen_types_history' AND object_id = OBJECT_ID(N'[dbo].[specimen_types]'))
    CREATE NONCLUSTERED INDEX [IX_specimen_types_history] ON [dbo].[specimen_types]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_specimen_types_is_active' AND object_id = OBJECT_ID(N'[dbo].[specimen_types]'))
    CREATE NONCLUSTERED INDEX [IX_specimen_types_is_active] ON [dbo].[specimen_types]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_specimen_types_created_date' AND object_id = OBJECT_ID(N'[dbo].[specimen_types]'))
    CREATE NONCLUSTERED INDEX [IX_specimen_types_created_date] ON [dbo].[specimen_types]([created_date] ASC);
-- lab_instruments
IF OBJECT_ID(N'[dbo].[lab_instruments]', N'U') IS NOT NULL
    DROP TABLE [dbo].[lab_instruments];
GO

CREATE TABLE [dbo].[lab_instruments] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [model] NVARCHAR(100) NULL,
    [manufacturer] NVARCHAR(100) NULL,
    [lab_section_id] BIGINT NULL,
    [serial_number] NVARCHAR(100) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_lab_instruments] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_lab_instruments_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[lab_instruments]'))
    ALTER TABLE [dbo].[lab_instruments] ADD CONSTRAINT [UQ_lab_instruments_code__tenant_id__history] UNIQUE (code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_instruments_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[lab_instruments]'))
    CREATE NONCLUSTERED INDEX [IX_lab_instruments_tenant_id] ON [dbo].[lab_instruments]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_instruments_lab_section_id' AND object_id = OBJECT_ID(N'[dbo].[lab_instruments]'))
    CREATE NONCLUSTERED INDEX [IX_lab_instruments_lab_section_id] ON [dbo].[lab_instruments]([lab_section_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_instruments_history' AND object_id = OBJECT_ID(N'[dbo].[lab_instruments]'))
    CREATE NONCLUSTERED INDEX [IX_lab_instruments_history] ON [dbo].[lab_instruments]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_instruments_is_active' AND object_id = OBJECT_ID(N'[dbo].[lab_instruments]'))
    CREATE NONCLUSTERED INDEX [IX_lab_instruments_is_active] ON [dbo].[lab_instruments]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_instruments_created_date' AND object_id = OBJECT_ID(N'[dbo].[lab_instruments]'))
    CREATE NONCLUSTERED INDEX [IX_lab_instruments_created_date] ON [dbo].[lab_instruments]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_lab_instruments_lab_section_id' AND parent_object_id = OBJECT_ID(N'[dbo].[lab_instruments]'))
    ALTER TABLE [dbo].[lab_instruments] ADD CONSTRAINT [FK_lab_instruments_lab_section_id] FOREIGN KEY ([lab_section_id]) REFERENCES [dbo].[lab_sections]([id]) ON DELETE NO ACTION;
-- lab_tests
IF OBJECT_ID(N'[dbo].[lab_tests]', N'U') IS NOT NULL
    DROP TABLE [dbo].[lab_tests];
GO

CREATE TABLE [dbo].[lab_tests] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(200) NOT NULL,
    [short_name] NVARCHAR(50) NULL,
    [lab_section_id] BIGINT NULL,
    [specimen_type_id] BIGINT NULL,
    [lab_unit_id] BIGINT NULL,
    [test_type] NVARCHAR(20) NOT NULL DEFAULT (N'Numeric'),
    [tat_minutes] INT NULL,
    [description] NVARCHAR(500) NULL,
    [standard_charge] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_lab_tests] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_lab_tests_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[lab_tests]'))
    ALTER TABLE [dbo].[lab_tests] ADD CONSTRAINT [UQ_lab_tests_code__tenant_id__history] UNIQUE (code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_tests_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[lab_tests]'))
    CREATE NONCLUSTERED INDEX [IX_lab_tests_tenant_id] ON [dbo].[lab_tests]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_tests_lab_section_id' AND object_id = OBJECT_ID(N'[dbo].[lab_tests]'))
    CREATE NONCLUSTERED INDEX [IX_lab_tests_lab_section_id] ON [dbo].[lab_tests]([lab_section_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_tests_specimen_type_id' AND object_id = OBJECT_ID(N'[dbo].[lab_tests]'))
    CREATE NONCLUSTERED INDEX [IX_lab_tests_specimen_type_id] ON [dbo].[lab_tests]([specimen_type_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_tests_lab_unit_id' AND object_id = OBJECT_ID(N'[dbo].[lab_tests]'))
    CREATE NONCLUSTERED INDEX [IX_lab_tests_lab_unit_id] ON [dbo].[lab_tests]([lab_unit_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_tests_history' AND object_id = OBJECT_ID(N'[dbo].[lab_tests]'))
    CREATE NONCLUSTERED INDEX [IX_lab_tests_history] ON [dbo].[lab_tests]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_tests_is_active' AND object_id = OBJECT_ID(N'[dbo].[lab_tests]'))
    CREATE NONCLUSTERED INDEX [IX_lab_tests_is_active] ON [dbo].[lab_tests]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_tests_created_date' AND object_id = OBJECT_ID(N'[dbo].[lab_tests]'))
    CREATE NONCLUSTERED INDEX [IX_lab_tests_created_date] ON [dbo].[lab_tests]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_lab_tests_lab_section_id' AND parent_object_id = OBJECT_ID(N'[dbo].[lab_tests]'))
    ALTER TABLE [dbo].[lab_tests] ADD CONSTRAINT [FK_lab_tests_lab_section_id] FOREIGN KEY ([lab_section_id]) REFERENCES [dbo].[lab_sections]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_lab_tests_specimen_type_id' AND parent_object_id = OBJECT_ID(N'[dbo].[lab_tests]'))
    ALTER TABLE [dbo].[lab_tests] ADD CONSTRAINT [FK_lab_tests_specimen_type_id] FOREIGN KEY ([specimen_type_id]) REFERENCES [dbo].[specimen_types]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_lab_tests_lab_unit_id' AND parent_object_id = OBJECT_ID(N'[dbo].[lab_tests]'))
    ALTER TABLE [dbo].[lab_tests] ADD CONSTRAINT [FK_lab_tests_lab_unit_id] FOREIGN KEY ([lab_unit_id]) REFERENCES [dbo].[lab_units]([id]) ON DELETE NO ACTION;
-- lab_panels
IF OBJECT_ID(N'[dbo].[lab_panels]', N'U') IS NOT NULL
    DROP TABLE [dbo].[lab_panels];
GO

CREATE TABLE [dbo].[lab_panels] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(200) NOT NULL,
    [description] NVARCHAR(500) NULL,
    [standard_charge] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [lab_section_id] BIGINT NULL,
    [tat_minutes] INT NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_lab_panels] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_lab_panels_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[lab_panels]'))
    ALTER TABLE [dbo].[lab_panels] ADD CONSTRAINT [UQ_lab_panels_code__tenant_id__history] UNIQUE (code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_panels_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[lab_panels]'))
    CREATE NONCLUSTERED INDEX [IX_lab_panels_tenant_id] ON [dbo].[lab_panels]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_panels_lab_section_id' AND object_id = OBJECT_ID(N'[dbo].[lab_panels]'))
    CREATE NONCLUSTERED INDEX [IX_lab_panels_lab_section_id] ON [dbo].[lab_panels]([lab_section_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_panels_history' AND object_id = OBJECT_ID(N'[dbo].[lab_panels]'))
    CREATE NONCLUSTERED INDEX [IX_lab_panels_history] ON [dbo].[lab_panels]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_panels_is_active' AND object_id = OBJECT_ID(N'[dbo].[lab_panels]'))
    CREATE NONCLUSTERED INDEX [IX_lab_panels_is_active] ON [dbo].[lab_panels]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_panels_created_date' AND object_id = OBJECT_ID(N'[dbo].[lab_panels]'))
    CREATE NONCLUSTERED INDEX [IX_lab_panels_created_date] ON [dbo].[lab_panels]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_lab_panels_lab_section_id' AND parent_object_id = OBJECT_ID(N'[dbo].[lab_panels]'))
    ALTER TABLE [dbo].[lab_panels] ADD CONSTRAINT [FK_lab_panels_lab_section_id] FOREIGN KEY ([lab_section_id]) REFERENCES [dbo].[lab_sections]([id]) ON DELETE NO ACTION;
-- lab_panel_tests
IF OBJECT_ID(N'[dbo].[lab_panel_tests]', N'U') IS NOT NULL
    DROP TABLE [dbo].[lab_panel_tests];
GO

CREATE TABLE [dbo].[lab_panel_tests] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [panel_id] BIGINT NOT NULL,
    [test_id] BIGINT NOT NULL,
    [display_order] INT NOT NULL DEFAULT (0),
    CONSTRAINT [PK_lab_panel_tests] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_lab_panel_tests_panel_id__test_id' AND object_id = OBJECT_ID(N'[dbo].[lab_panel_tests]'))
    ALTER TABLE [dbo].[lab_panel_tests] ADD CONSTRAINT [UQ_lab_panel_tests_panel_id__test_id] UNIQUE (panel_id, test_id);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_panel_tests_panel_id' AND object_id = OBJECT_ID(N'[dbo].[lab_panel_tests]'))
    CREATE NONCLUSTERED INDEX [IX_lab_panel_tests_panel_id] ON [dbo].[lab_panel_tests]([panel_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_panel_tests_test_id' AND object_id = OBJECT_ID(N'[dbo].[lab_panel_tests]'))
    CREATE NONCLUSTERED INDEX [IX_lab_panel_tests_test_id] ON [dbo].[lab_panel_tests]([test_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_lab_panel_tests_panel_id' AND parent_object_id = OBJECT_ID(N'[dbo].[lab_panel_tests]'))
    ALTER TABLE [dbo].[lab_panel_tests] ADD CONSTRAINT [FK_lab_panel_tests_panel_id] FOREIGN KEY ([panel_id]) REFERENCES [dbo].[lab_panels]([id]) ON DELETE CASCADE;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_lab_panel_tests_test_id' AND parent_object_id = OBJECT_ID(N'[dbo].[lab_panel_tests]'))
    ALTER TABLE [dbo].[lab_panel_tests] ADD CONSTRAINT [FK_lab_panel_tests_test_id] FOREIGN KEY ([test_id]) REFERENCES [dbo].[lab_tests]([id]) ON DELETE CASCADE;
-- reference_ranges
IF OBJECT_ID(N'[dbo].[reference_ranges]', N'U') IS NOT NULL
    DROP TABLE [dbo].[reference_ranges];
GO

CREATE TABLE [dbo].[reference_ranges] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [lab_test_id] BIGINT NOT NULL,
    [gender] NVARCHAR(10) NOT NULL DEFAULT (N'Any'),
    [age_min_years] DECIMAL(5,1) NOT NULL DEFAULT (0),
    [age_max_years] DECIMAL(5,1) NOT NULL DEFAULT (999.9),
    [min_value] DECIMAL(18,6) NULL,
    [max_value] DECIMAL(18,6) NULL,
    [normal_values_text] NVARCHAR(255) NULL,
    [interpretation] NVARCHAR(255) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_reference_ranges] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_reference_ranges_lab_test_id__gender__age_min_years__age_max_years__history' AND object_id = OBJECT_ID(N'[dbo].[reference_ranges]'))
    ALTER TABLE [dbo].[reference_ranges] ADD CONSTRAINT [UQ_reference_ranges_lab_test_id__gender__age_min_years__age_max_years__history] UNIQUE (lab_test_id, gender, age_min_years, age_max_years, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_reference_ranges_lab_test_id' AND object_id = OBJECT_ID(N'[dbo].[reference_ranges]'))
    CREATE NONCLUSTERED INDEX [IX_reference_ranges_lab_test_id] ON [dbo].[reference_ranges]([lab_test_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_reference_ranges_history' AND object_id = OBJECT_ID(N'[dbo].[reference_ranges]'))
    CREATE NONCLUSTERED INDEX [IX_reference_ranges_history] ON [dbo].[reference_ranges]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_reference_ranges_is_active' AND object_id = OBJECT_ID(N'[dbo].[reference_ranges]'))
    CREATE NONCLUSTERED INDEX [IX_reference_ranges_is_active] ON [dbo].[reference_ranges]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_reference_ranges_created_date' AND object_id = OBJECT_ID(N'[dbo].[reference_ranges]'))
    CREATE NONCLUSTERED INDEX [IX_reference_ranges_created_date] ON [dbo].[reference_ranges]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_reference_ranges_lab_test_id' AND parent_object_id = OBJECT_ID(N'[dbo].[reference_ranges]'))
    ALTER TABLE [dbo].[reference_ranges] ADD CONSTRAINT [FK_reference_ranges_lab_test_id] FOREIGN KEY ([lab_test_id]) REFERENCES [dbo].[lab_tests]([id]) ON DELETE CASCADE;
-- supply_units
IF OBJECT_ID(N'[dbo].[supply_units]', N'U') IS NOT NULL
    DROP TABLE [dbo].[supply_units];
GO

CREATE TABLE [dbo].[supply_units] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [description] NVARCHAR(255) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_supply_units] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_supply_units_history' AND object_id = OBJECT_ID(N'[dbo].[supply_units]'))
    CREATE NONCLUSTERED INDEX [IX_supply_units_history] ON [dbo].[supply_units]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_supply_units_is_active' AND object_id = OBJECT_ID(N'[dbo].[supply_units]'))
    CREATE NONCLUSTERED INDEX [IX_supply_units_is_active] ON [dbo].[supply_units]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_supply_units_created_date' AND object_id = OBJECT_ID(N'[dbo].[supply_units]'))
    CREATE NONCLUSTERED INDEX [IX_supply_units_created_date] ON [dbo].[supply_units]([created_date] ASC);
-- item_categories
IF OBJECT_ID(N'[dbo].[item_categories]', N'U') IS NOT NULL
    DROP TABLE [dbo].[item_categories];
GO

CREATE TABLE [dbo].[item_categories] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [description] NVARCHAR(255) NULL,
    [parent_id] BIGINT NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_item_categories] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_item_categories_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[item_categories]'))
    ALTER TABLE [dbo].[item_categories] ADD CONSTRAINT [UQ_item_categories_code__tenant_id__history] UNIQUE (code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_item_categories_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[item_categories]'))
    CREATE NONCLUSTERED INDEX [IX_item_categories_tenant_id] ON [dbo].[item_categories]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_item_categories_parent_id' AND object_id = OBJECT_ID(N'[dbo].[item_categories]'))
    CREATE NONCLUSTERED INDEX [IX_item_categories_parent_id] ON [dbo].[item_categories]([parent_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_item_categories_history' AND object_id = OBJECT_ID(N'[dbo].[item_categories]'))
    CREATE NONCLUSTERED INDEX [IX_item_categories_history] ON [dbo].[item_categories]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_item_categories_is_active' AND object_id = OBJECT_ID(N'[dbo].[item_categories]'))
    CREATE NONCLUSTERED INDEX [IX_item_categories_is_active] ON [dbo].[item_categories]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_item_categories_created_date' AND object_id = OBJECT_ID(N'[dbo].[item_categories]'))
    CREATE NONCLUSTERED INDEX [IX_item_categories_created_date] ON [dbo].[item_categories]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_item_categories_parent_id' AND parent_object_id = OBJECT_ID(N'[dbo].[item_categories]'))
    ALTER TABLE [dbo].[item_categories] ADD CONSTRAINT [FK_item_categories_parent_id] FOREIGN KEY ([parent_id]) REFERENCES [dbo].[item_categories]([id]) ON DELETE NO ACTION;
-- suppliers
IF OBJECT_ID(N'[dbo].[suppliers]', N'U') IS NOT NULL
    DROP TABLE [dbo].[suppliers];
GO

CREATE TABLE [dbo].[suppliers] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(150) NOT NULL,
    [contact_person] NVARCHAR(100) NULL,
    [contact_number] NVARCHAR(30) NULL,
    [email] NVARCHAR(100) NULL,
    [address] NVARCHAR(255) NULL,
    [tin] NVARCHAR(30) NULL,
    [payment_terms] NVARCHAR(100) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_suppliers] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_suppliers_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[suppliers]'))
    ALTER TABLE [dbo].[suppliers] ADD CONSTRAINT [UQ_suppliers_code__tenant_id__history] UNIQUE (code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_suppliers_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[suppliers]'))
    CREATE NONCLUSTERED INDEX [IX_suppliers_tenant_id] ON [dbo].[suppliers]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_suppliers_history' AND object_id = OBJECT_ID(N'[dbo].[suppliers]'))
    CREATE NONCLUSTERED INDEX [IX_suppliers_history] ON [dbo].[suppliers]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_suppliers_is_active' AND object_id = OBJECT_ID(N'[dbo].[suppliers]'))
    CREATE NONCLUSTERED INDEX [IX_suppliers_is_active] ON [dbo].[suppliers]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_suppliers_created_date' AND object_id = OBJECT_ID(N'[dbo].[suppliers]'))
    CREATE NONCLUSTERED INDEX [IX_suppliers_created_date] ON [dbo].[suppliers]([created_date] ASC);
-- stock_locations
IF OBJECT_ID(N'[dbo].[stock_locations]', N'U') IS NOT NULL
    DROP TABLE [dbo].[stock_locations];
GO

CREATE TABLE [dbo].[stock_locations] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [description] NVARCHAR(255) NULL,
    [type] NVARCHAR(30) NOT NULL DEFAULT (N'Room'),
    [parent_id] BIGINT NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_stock_locations] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_stock_locations_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[stock_locations]'))
    ALTER TABLE [dbo].[stock_locations] ADD CONSTRAINT [UQ_stock_locations_code__tenant_id__history] UNIQUE (code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_stock_locations_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[stock_locations]'))
    CREATE NONCLUSTERED INDEX [IX_stock_locations_tenant_id] ON [dbo].[stock_locations]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_stock_locations_parent_id' AND object_id = OBJECT_ID(N'[dbo].[stock_locations]'))
    CREATE NONCLUSTERED INDEX [IX_stock_locations_parent_id] ON [dbo].[stock_locations]([parent_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_stock_locations_history' AND object_id = OBJECT_ID(N'[dbo].[stock_locations]'))
    CREATE NONCLUSTERED INDEX [IX_stock_locations_history] ON [dbo].[stock_locations]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_stock_locations_is_active' AND object_id = OBJECT_ID(N'[dbo].[stock_locations]'))
    CREATE NONCLUSTERED INDEX [IX_stock_locations_is_active] ON [dbo].[stock_locations]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_stock_locations_created_date' AND object_id = OBJECT_ID(N'[dbo].[stock_locations]'))
    CREATE NONCLUSTERED INDEX [IX_stock_locations_created_date] ON [dbo].[stock_locations]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_stock_locations_parent_id' AND parent_object_id = OBJECT_ID(N'[dbo].[stock_locations]'))
    ALTER TABLE [dbo].[stock_locations] ADD CONSTRAINT [FK_stock_locations_parent_id] FOREIGN KEY ([parent_id]) REFERENCES [dbo].[stock_locations]([id]) ON DELETE NO ACTION;
-- items
IF OBJECT_ID(N'[dbo].[items]', N'U') IS NOT NULL
    DROP TABLE [dbo].[items];
GO

CREATE TABLE [dbo].[items] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [code] NVARCHAR(30) NOT NULL,
    [name] NVARCHAR(200) NOT NULL,
    [description] NVARCHAR(500) NULL,
    [item_category_id] BIGINT NULL,
    [supplier_id] BIGINT NULL,
    [supply_unit_id] BIGINT NULL,
    [sku] NVARCHAR(50) NULL,
    [unit_cost] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [reorder_level] INT NOT NULL DEFAULT (0),
    [reorder_qty] INT NOT NULL DEFAULT (0),
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_items] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_items_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[items]'))
    ALTER TABLE [dbo].[items] ADD CONSTRAINT [UQ_items_code__tenant_id__history] UNIQUE (code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_items_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[items]'))
    CREATE NONCLUSTERED INDEX [IX_items_tenant_id] ON [dbo].[items]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_items_item_category_id' AND object_id = OBJECT_ID(N'[dbo].[items]'))
    CREATE NONCLUSTERED INDEX [IX_items_item_category_id] ON [dbo].[items]([item_category_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_items_supplier_id' AND object_id = OBJECT_ID(N'[dbo].[items]'))
    CREATE NONCLUSTERED INDEX [IX_items_supplier_id] ON [dbo].[items]([supplier_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_items_supply_unit_id' AND object_id = OBJECT_ID(N'[dbo].[items]'))
    CREATE NONCLUSTERED INDEX [IX_items_supply_unit_id] ON [dbo].[items]([supply_unit_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_items_history' AND object_id = OBJECT_ID(N'[dbo].[items]'))
    CREATE NONCLUSTERED INDEX [IX_items_history] ON [dbo].[items]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_items_is_active' AND object_id = OBJECT_ID(N'[dbo].[items]'))
    CREATE NONCLUSTERED INDEX [IX_items_is_active] ON [dbo].[items]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_items_created_date' AND object_id = OBJECT_ID(N'[dbo].[items]'))
    CREATE NONCLUSTERED INDEX [IX_items_created_date] ON [dbo].[items]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_items_item_category_id' AND parent_object_id = OBJECT_ID(N'[dbo].[items]'))
    ALTER TABLE [dbo].[items] ADD CONSTRAINT [FK_items_item_category_id] FOREIGN KEY ([item_category_id]) REFERENCES [dbo].[item_categories]([id]) ON DELETE SET NULL;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_items_supplier_id' AND parent_object_id = OBJECT_ID(N'[dbo].[items]'))
    ALTER TABLE [dbo].[items] ADD CONSTRAINT [FK_items_supplier_id] FOREIGN KEY ([supplier_id]) REFERENCES [dbo].[suppliers]([id]) ON DELETE SET NULL;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_items_supply_unit_id' AND parent_object_id = OBJECT_ID(N'[dbo].[items]'))
    ALTER TABLE [dbo].[items] ADD CONSTRAINT [FK_items_supply_unit_id] FOREIGN KEY ([supply_unit_id]) REFERENCES [dbo].[supply_units]([id]) ON DELETE SET NULL;
-- item_batches
IF OBJECT_ID(N'[dbo].[item_batches]', N'U') IS NOT NULL
    DROP TABLE [dbo].[item_batches];
GO

CREATE TABLE [dbo].[item_batches] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [item_id] BIGINT NOT NULL,
    [batch_no] NVARCHAR(50) NOT NULL,
    [expiry_date] DATE NULL,
    [quantity_on_hand] INT NOT NULL DEFAULT (0),
    [unit_cost] DECIMAL(12,2) NULL,
    [selling_price] DECIMAL(12,2) NULL,
    [location] NVARCHAR(255) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_item_batches] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_item_batches_tenant_id__item_id__batch_no' AND object_id = OBJECT_ID(N'[dbo].[item_batches]'))
    ALTER TABLE [dbo].[item_batches] ADD CONSTRAINT [UQ_item_batches_tenant_id__item_id__batch_no] UNIQUE (tenant_id, item_id, batch_no);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_item_batches_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[item_batches]'))
    CREATE NONCLUSTERED INDEX [IX_item_batches_tenant_id] ON [dbo].[item_batches]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_item_batches_item_id' AND object_id = OBJECT_ID(N'[dbo].[item_batches]'))
    CREATE NONCLUSTERED INDEX [IX_item_batches_item_id] ON [dbo].[item_batches]([item_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_item_batches_history' AND object_id = OBJECT_ID(N'[dbo].[item_batches]'))
    CREATE NONCLUSTERED INDEX [IX_item_batches_history] ON [dbo].[item_batches]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_item_batches_is_active' AND object_id = OBJECT_ID(N'[dbo].[item_batches]'))
    CREATE NONCLUSTERED INDEX [IX_item_batches_is_active] ON [dbo].[item_batches]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_item_batches_created_date' AND object_id = OBJECT_ID(N'[dbo].[item_batches]'))
    CREATE NONCLUSTERED INDEX [IX_item_batches_created_date] ON [dbo].[item_batches]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_item_batches_item_id' AND parent_object_id = OBJECT_ID(N'[dbo].[item_batches]'))
    ALTER TABLE [dbo].[item_batches] ADD CONSTRAINT [FK_item_batches_item_id] FOREIGN KEY ([item_id]) REFERENCES [dbo].[items]([id]) ON DELETE CASCADE;
-- item_stock_adjustments
IF OBJECT_ID(N'[dbo].[item_stock_adjustments]', N'U') IS NOT NULL
    DROP TABLE [dbo].[item_stock_adjustments];
GO

CREATE TABLE [dbo].[item_stock_adjustments] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [item_batch_id] BIGINT NOT NULL,
    [type] NVARCHAR(50) NOT NULL,
    [quantity] INT NOT NULL,
    [reason] NVARCHAR(MAX) NULL,
    [adjusted_by] BIGINT NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_item_stock_adjustments] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_item_stock_adjustments_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[item_stock_adjustments]'))
    CREATE NONCLUSTERED INDEX [IX_item_stock_adjustments_tenant_id] ON [dbo].[item_stock_adjustments]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_item_stock_adjustments_item_batch_id' AND object_id = OBJECT_ID(N'[dbo].[item_stock_adjustments]'))
    CREATE NONCLUSTERED INDEX [IX_item_stock_adjustments_item_batch_id] ON [dbo].[item_stock_adjustments]([item_batch_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_item_stock_adjustments_adjusted_by' AND object_id = OBJECT_ID(N'[dbo].[item_stock_adjustments]'))
    CREATE NONCLUSTERED INDEX [IX_item_stock_adjustments_adjusted_by] ON [dbo].[item_stock_adjustments]([adjusted_by] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_item_stock_adjustments_history' AND object_id = OBJECT_ID(N'[dbo].[item_stock_adjustments]'))
    CREATE NONCLUSTERED INDEX [IX_item_stock_adjustments_history] ON [dbo].[item_stock_adjustments]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_item_stock_adjustments_is_active' AND object_id = OBJECT_ID(N'[dbo].[item_stock_adjustments]'))
    CREATE NONCLUSTERED INDEX [IX_item_stock_adjustments_is_active] ON [dbo].[item_stock_adjustments]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_item_stock_adjustments_created_date' AND object_id = OBJECT_ID(N'[dbo].[item_stock_adjustments]'))
    CREATE NONCLUSTERED INDEX [IX_item_stock_adjustments_created_date] ON [dbo].[item_stock_adjustments]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_item_stock_adjustments_item_batch_id' AND parent_object_id = OBJECT_ID(N'[dbo].[item_stock_adjustments]'))
    ALTER TABLE [dbo].[item_stock_adjustments] ADD CONSTRAINT [FK_item_stock_adjustments_item_batch_id] FOREIGN KEY ([item_batch_id]) REFERENCES [dbo].[item_batches]([id]) ON DELETE CASCADE;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_item_stock_adjustments_adjusted_by' AND parent_object_id = OBJECT_ID(N'[dbo].[item_stock_adjustments]'))
    ALTER TABLE [dbo].[item_stock_adjustments] ADD CONSTRAINT [FK_item_stock_adjustments_adjusted_by] FOREIGN KEY ([adjusted_by]) REFERENCES [dbo].[users]([id]) ON DELETE NO ACTION;
-- procedure_categories
IF OBJECT_ID(N'[dbo].[procedure_categories]', N'U') IS NOT NULL
    DROP TABLE [dbo].[procedure_categories];
GO

CREATE TABLE [dbo].[procedure_categories] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [description] NVARCHAR(255) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_procedure_categories] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_procedure_categories_history' AND object_id = OBJECT_ID(N'[dbo].[procedure_categories]'))
    CREATE NONCLUSTERED INDEX [IX_procedure_categories_history] ON [dbo].[procedure_categories]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_procedure_categories_is_active' AND object_id = OBJECT_ID(N'[dbo].[procedure_categories]'))
    CREATE NONCLUSTERED INDEX [IX_procedure_categories_is_active] ON [dbo].[procedure_categories]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_procedure_categories_created_date' AND object_id = OBJECT_ID(N'[dbo].[procedure_categories]'))
    CREATE NONCLUSTERED INDEX [IX_procedure_categories_created_date] ON [dbo].[procedure_categories]([created_date] ASC);
-- procedures
IF OBJECT_ID(N'[dbo].[procedures]', N'U') IS NOT NULL
    DROP TABLE [dbo].[procedures];
GO

CREATE TABLE [dbo].[procedures] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [code] NVARCHAR(30) NOT NULL,
    [name] NVARCHAR(200) NOT NULL,
    [description] NVARCHAR(500) NULL,
    [category_id] BIGINT NULL,
    [department_id] BIGINT NULL,
    [standard_charge] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_procedures] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_procedures_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[procedures]'))
    ALTER TABLE [dbo].[procedures] ADD CONSTRAINT [UQ_procedures_code__tenant_id__history] UNIQUE (code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_procedures_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[procedures]'))
    CREATE NONCLUSTERED INDEX [IX_procedures_tenant_id] ON [dbo].[procedures]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_procedures_category_id' AND object_id = OBJECT_ID(N'[dbo].[procedures]'))
    CREATE NONCLUSTERED INDEX [IX_procedures_category_id] ON [dbo].[procedures]([category_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_procedures_department_id' AND object_id = OBJECT_ID(N'[dbo].[procedures]'))
    CREATE NONCLUSTERED INDEX [IX_procedures_department_id] ON [dbo].[procedures]([department_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_procedures_history' AND object_id = OBJECT_ID(N'[dbo].[procedures]'))
    CREATE NONCLUSTERED INDEX [IX_procedures_history] ON [dbo].[procedures]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_procedures_is_active' AND object_id = OBJECT_ID(N'[dbo].[procedures]'))
    CREATE NONCLUSTERED INDEX [IX_procedures_is_active] ON [dbo].[procedures]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_procedures_created_date' AND object_id = OBJECT_ID(N'[dbo].[procedures]'))
    CREATE NONCLUSTERED INDEX [IX_procedures_created_date] ON [dbo].[procedures]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_procedures_category_id' AND parent_object_id = OBJECT_ID(N'[dbo].[procedures]'))
    ALTER TABLE [dbo].[procedures] ADD CONSTRAINT [FK_procedures_category_id] FOREIGN KEY ([category_id]) REFERENCES [dbo].[procedure_categories]([id]) ON DELETE SET NULL;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_procedures_department_id' AND parent_object_id = OBJECT_ID(N'[dbo].[procedures]'))
    ALTER TABLE [dbo].[procedures] ADD CONSTRAINT [FK_procedures_department_id] FOREIGN KEY ([department_id]) REFERENCES [dbo].[departments]([id]) ON DELETE SET NULL;
-- dialysis_types
IF OBJECT_ID(N'[dbo].[dialysis_types]', N'U') IS NOT NULL
    DROP TABLE [dbo].[dialysis_types];
GO

CREATE TABLE [dbo].[dialysis_types] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [description] NVARCHAR(255) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_dialysis_types] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_dialysis_types_history' AND object_id = OBJECT_ID(N'[dbo].[dialysis_types]'))
    CREATE NONCLUSTERED INDEX [IX_dialysis_types_history] ON [dbo].[dialysis_types]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_dialysis_types_is_active' AND object_id = OBJECT_ID(N'[dbo].[dialysis_types]'))
    CREATE NONCLUSTERED INDEX [IX_dialysis_types_is_active] ON [dbo].[dialysis_types]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_dialysis_types_created_date' AND object_id = OBJECT_ID(N'[dbo].[dialysis_types]'))
    CREATE NONCLUSTERED INDEX [IX_dialysis_types_created_date] ON [dbo].[dialysis_types]([created_date] ASC);
-- dialysis_sessions
IF OBJECT_ID(N'[dbo].[dialysis_sessions]', N'U') IS NOT NULL
    DROP TABLE [dbo].[dialysis_sessions];
GO

CREATE TABLE [dbo].[dialysis_sessions] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [code] NVARCHAR(30) NOT NULL,
    [patient_id] BIGINT NOT NULL,
    [dialysis_type_id] BIGINT NULL,
    [access_type] NVARCHAR(50) NULL,
    [session_date] DATE NULL,
    [pre_weight] DECIMAL(6,2) NULL,
    [post_weight] DECIMAL(6,2) NULL,
    [pre_bp] NVARCHAR(15) NULL,
    [post_bp] NVARCHAR(15) NULL,
    [heart_rate] INT NULL,
    [medications] NVARCHAR(MAX) NULL,
    [notes] NVARCHAR(MAX) NULL,
    [history_of_present_illness] NVARCHAR(MAX) NULL,
    [physical_exam_findings] NVARCHAR(MAX) NULL,
    [assessment] NVARCHAR(MAX) NULL,
    [orders_summary] NVARCHAR(MAX) NULL,
    [treatment_notes] NVARCHAR(MAX) NULL,
    [disposition] NVARCHAR(30) NOT NULL DEFAULT (N'Pending'),
    [discharge_instructions] NVARCHAR(MAX) NULL,
    [disposition_datetime] DATETIME2 NULL,
    [status] NVARCHAR(30) NOT NULL DEFAULT (N'Scheduled'),
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_dialysis_sessions] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_dialysis_sessions_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[dialysis_sessions]'))
    ALTER TABLE [dbo].[dialysis_sessions] ADD CONSTRAINT [UQ_dialysis_sessions_code__tenant_id__history] UNIQUE (code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_dialysis_sessions_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[dialysis_sessions]'))
    CREATE NONCLUSTERED INDEX [IX_dialysis_sessions_tenant_id] ON [dbo].[dialysis_sessions]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_dialysis_sessions_patient_id' AND object_id = OBJECT_ID(N'[dbo].[dialysis_sessions]'))
    CREATE NONCLUSTERED INDEX [IX_dialysis_sessions_patient_id] ON [dbo].[dialysis_sessions]([patient_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_dialysis_sessions_dialysis_type_id' AND object_id = OBJECT_ID(N'[dbo].[dialysis_sessions]'))
    CREATE NONCLUSTERED INDEX [IX_dialysis_sessions_dialysis_type_id] ON [dbo].[dialysis_sessions]([dialysis_type_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_dialysis_sessions_history' AND object_id = OBJECT_ID(N'[dbo].[dialysis_sessions]'))
    CREATE NONCLUSTERED INDEX [IX_dialysis_sessions_history] ON [dbo].[dialysis_sessions]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_dialysis_sessions_is_active' AND object_id = OBJECT_ID(N'[dbo].[dialysis_sessions]'))
    CREATE NONCLUSTERED INDEX [IX_dialysis_sessions_is_active] ON [dbo].[dialysis_sessions]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_dialysis_sessions_created_date' AND object_id = OBJECT_ID(N'[dbo].[dialysis_sessions]'))
    CREATE NONCLUSTERED INDEX [IX_dialysis_sessions_created_date] ON [dbo].[dialysis_sessions]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_dialysis_sessions_status' AND object_id = OBJECT_ID(N'[dbo].[dialysis_sessions]'))
    CREATE NONCLUSTERED INDEX [IX_dialysis_sessions_status] ON [dbo].[dialysis_sessions]([status] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_dialysis_sessions_patient_id' AND parent_object_id = OBJECT_ID(N'[dbo].[dialysis_sessions]'))
    ALTER TABLE [dbo].[dialysis_sessions] ADD CONSTRAINT [FK_dialysis_sessions_patient_id] FOREIGN KEY ([patient_id]) REFERENCES [dbo].[patients]([id]) ON DELETE CASCADE;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_dialysis_sessions_dialysis_type_id' AND parent_object_id = OBJECT_ID(N'[dbo].[dialysis_sessions]'))
    ALTER TABLE [dbo].[dialysis_sessions] ADD CONSTRAINT [FK_dialysis_sessions_dialysis_type_id] FOREIGN KEY ([dialysis_type_id]) REFERENCES [dbo].[dialysis_types]([id]) ON DELETE SET NULL;
-- radiology_modalities
IF OBJECT_ID(N'[dbo].[radiology_modalities]', N'U') IS NOT NULL
    DROP TABLE [dbo].[radiology_modalities];
GO

CREATE TABLE [dbo].[radiology_modalities] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [description] NVARCHAR(255) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_radiology_modalities] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_radiology_modalities_history' AND object_id = OBJECT_ID(N'[dbo].[radiology_modalities]'))
    CREATE NONCLUSTERED INDEX [IX_radiology_modalities_history] ON [dbo].[radiology_modalities]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_radiology_modalities_is_active' AND object_id = OBJECT_ID(N'[dbo].[radiology_modalities]'))
    CREATE NONCLUSTERED INDEX [IX_radiology_modalities_is_active] ON [dbo].[radiology_modalities]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_radiology_modalities_created_date' AND object_id = OBJECT_ID(N'[dbo].[radiology_modalities]'))
    CREATE NONCLUSTERED INDEX [IX_radiology_modalities_created_date] ON [dbo].[radiology_modalities]([created_date] ASC);
-- radiology_body_parts
IF OBJECT_ID(N'[dbo].[radiology_body_parts]', N'U') IS NOT NULL
    DROP TABLE [dbo].[radiology_body_parts];
GO

CREATE TABLE [dbo].[radiology_body_parts] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [description] NVARCHAR(255) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_radiology_body_parts] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_radiology_body_parts_history' AND object_id = OBJECT_ID(N'[dbo].[radiology_body_parts]'))
    CREATE NONCLUSTERED INDEX [IX_radiology_body_parts_history] ON [dbo].[radiology_body_parts]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_radiology_body_parts_is_active' AND object_id = OBJECT_ID(N'[dbo].[radiology_body_parts]'))
    CREATE NONCLUSTERED INDEX [IX_radiology_body_parts_is_active] ON [dbo].[radiology_body_parts]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_radiology_body_parts_created_date' AND object_id = OBJECT_ID(N'[dbo].[radiology_body_parts]'))
    CREATE NONCLUSTERED INDEX [IX_radiology_body_parts_created_date] ON [dbo].[radiology_body_parts]([created_date] ASC);
-- radiology_exam_types
IF OBJECT_ID(N'[dbo].[radiology_exam_types]', N'U') IS NOT NULL
    DROP TABLE [dbo].[radiology_exam_types];
GO

CREATE TABLE [dbo].[radiology_exam_types] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [code] NVARCHAR(30) NOT NULL,
    [name] NVARCHAR(200) NOT NULL,
    [description] NVARCHAR(500) NULL,
    [modality_id] BIGINT NULL,
    [body_part_id] BIGINT NULL,
    [department_id] BIGINT NULL,
    [standard_charge] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [preparation] NVARCHAR(500) NULL,
    [contrast_type] NVARCHAR(50) NULL,
    [tat_minutes] INT NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_radiology_exam_types] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_radiology_exam_types_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[radiology_exam_types]'))
    ALTER TABLE [dbo].[radiology_exam_types] ADD CONSTRAINT [UQ_radiology_exam_types_code__tenant_id__history] UNIQUE (code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_radiology_exam_types_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[radiology_exam_types]'))
    CREATE NONCLUSTERED INDEX [IX_radiology_exam_types_tenant_id] ON [dbo].[radiology_exam_types]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_radiology_exam_types_modality_id' AND object_id = OBJECT_ID(N'[dbo].[radiology_exam_types]'))
    CREATE NONCLUSTERED INDEX [IX_radiology_exam_types_modality_id] ON [dbo].[radiology_exam_types]([modality_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_radiology_exam_types_body_part_id' AND object_id = OBJECT_ID(N'[dbo].[radiology_exam_types]'))
    CREATE NONCLUSTERED INDEX [IX_radiology_exam_types_body_part_id] ON [dbo].[radiology_exam_types]([body_part_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_radiology_exam_types_department_id' AND object_id = OBJECT_ID(N'[dbo].[radiology_exam_types]'))
    CREATE NONCLUSTERED INDEX [IX_radiology_exam_types_department_id] ON [dbo].[radiology_exam_types]([department_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_radiology_exam_types_history' AND object_id = OBJECT_ID(N'[dbo].[radiology_exam_types]'))
    CREATE NONCLUSTERED INDEX [IX_radiology_exam_types_history] ON [dbo].[radiology_exam_types]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_radiology_exam_types_is_active' AND object_id = OBJECT_ID(N'[dbo].[radiology_exam_types]'))
    CREATE NONCLUSTERED INDEX [IX_radiology_exam_types_is_active] ON [dbo].[radiology_exam_types]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_radiology_exam_types_created_date' AND object_id = OBJECT_ID(N'[dbo].[radiology_exam_types]'))
    CREATE NONCLUSTERED INDEX [IX_radiology_exam_types_created_date] ON [dbo].[radiology_exam_types]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_radiology_exam_types_modality_id' AND parent_object_id = OBJECT_ID(N'[dbo].[radiology_exam_types]'))
    ALTER TABLE [dbo].[radiology_exam_types] ADD CONSTRAINT [FK_radiology_exam_types_modality_id] FOREIGN KEY ([modality_id]) REFERENCES [dbo].[radiology_modalities]([id]) ON DELETE SET NULL;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_radiology_exam_types_body_part_id' AND parent_object_id = OBJECT_ID(N'[dbo].[radiology_exam_types]'))
    ALTER TABLE [dbo].[radiology_exam_types] ADD CONSTRAINT [FK_radiology_exam_types_body_part_id] FOREIGN KEY ([body_part_id]) REFERENCES [dbo].[radiology_body_parts]([id]) ON DELETE SET NULL;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_radiology_exam_types_department_id' AND parent_object_id = OBJECT_ID(N'[dbo].[radiology_exam_types]'))
    ALTER TABLE [dbo].[radiology_exam_types] ADD CONSTRAINT [FK_radiology_exam_types_department_id] FOREIGN KEY ([department_id]) REFERENCES [dbo].[departments]([id]) ON DELETE SET NULL;
-- micu_assessment_parameters
IF OBJECT_ID(N'[dbo].[micu_assessment_parameters]', N'U') IS NOT NULL
    DROP TABLE [dbo].[micu_assessment_parameters];
GO

CREATE TABLE [dbo].[micu_assessment_parameters] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [description] NVARCHAR(255) NULL,
    [category] NVARCHAR(50) NULL,
    [default_unit] NVARCHAR(30) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_micu_assessment_parameters] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_micu_assessment_parameters_history' AND object_id = OBJECT_ID(N'[dbo].[micu_assessment_parameters]'))
    CREATE NONCLUSTERED INDEX [IX_micu_assessment_parameters_history] ON [dbo].[micu_assessment_parameters]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_micu_assessment_parameters_is_active' AND object_id = OBJECT_ID(N'[dbo].[micu_assessment_parameters]'))
    CREATE NONCLUSTERED INDEX [IX_micu_assessment_parameters_is_active] ON [dbo].[micu_assessment_parameters]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_micu_assessment_parameters_created_date' AND object_id = OBJECT_ID(N'[dbo].[micu_assessment_parameters]'))
    CREATE NONCLUSTERED INDEX [IX_micu_assessment_parameters_created_date] ON [dbo].[micu_assessment_parameters]([created_date] ASC);
-- micu_assessments
IF OBJECT_ID(N'[dbo].[micu_assessments]', N'U') IS NOT NULL
    DROP TABLE [dbo].[micu_assessments];
GO

CREATE TABLE [dbo].[micu_assessments] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [admission_id] BIGINT NOT NULL,
    [assessed_by] BIGINT NOT NULL,
    [assessed_at] DATETIME2 NOT NULL,
    [notes] NVARCHAR(MAX) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_micu_assessments] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_micu_assessments_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[micu_assessments]'))
    CREATE NONCLUSTERED INDEX [IX_micu_assessments_tenant_id] ON [dbo].[micu_assessments]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_micu_assessments_admission_id' AND object_id = OBJECT_ID(N'[dbo].[micu_assessments]'))
    CREATE NONCLUSTERED INDEX [IX_micu_assessments_admission_id] ON [dbo].[micu_assessments]([admission_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_micu_assessments_assessed_by' AND object_id = OBJECT_ID(N'[dbo].[micu_assessments]'))
    CREATE NONCLUSTERED INDEX [IX_micu_assessments_assessed_by] ON [dbo].[micu_assessments]([assessed_by] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_micu_assessments_history' AND object_id = OBJECT_ID(N'[dbo].[micu_assessments]'))
    CREATE NONCLUSTERED INDEX [IX_micu_assessments_history] ON [dbo].[micu_assessments]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_micu_assessments_is_active' AND object_id = OBJECT_ID(N'[dbo].[micu_assessments]'))
    CREATE NONCLUSTERED INDEX [IX_micu_assessments_is_active] ON [dbo].[micu_assessments]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_micu_assessments_created_date' AND object_id = OBJECT_ID(N'[dbo].[micu_assessments]'))
    CREATE NONCLUSTERED INDEX [IX_micu_assessments_created_date] ON [dbo].[micu_assessments]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_micu_assessments_admission_id' AND parent_object_id = OBJECT_ID(N'[dbo].[micu_assessments]'))
    ALTER TABLE [dbo].[micu_assessments] ADD CONSTRAINT [FK_micu_assessments_admission_id] FOREIGN KEY ([admission_id]) REFERENCES [dbo].[admissions]([id]) ON DELETE CASCADE;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_micu_assessments_assessed_by' AND parent_object_id = OBJECT_ID(N'[dbo].[micu_assessments]'))
    ALTER TABLE [dbo].[micu_assessments] ADD CONSTRAINT [FK_micu_assessments_assessed_by] FOREIGN KEY ([assessed_by]) REFERENCES [dbo].[health_care_professionals]([id]) ON DELETE NO ACTION;
-- micu_assessment_values
IF OBJECT_ID(N'[dbo].[micu_assessment_values]', N'U') IS NOT NULL
    DROP TABLE [dbo].[micu_assessment_values];
GO

CREATE TABLE [dbo].[micu_assessment_values] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [micu_assessment_id] BIGINT NOT NULL,
    [micu_assessment_parameter_id] BIGINT NOT NULL,
    [value] NVARCHAR(50) NULL,
    [unit] NVARCHAR(30) NULL,
    CONSTRAINT [PK_micu_assessment_values] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_micu_assessment_values_micu_assessment_id' AND object_id = OBJECT_ID(N'[dbo].[micu_assessment_values]'))
    CREATE NONCLUSTERED INDEX [IX_micu_assessment_values_micu_assessment_id] ON [dbo].[micu_assessment_values]([micu_assessment_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_micu_assessment_values_micu_assessment_parameter_id' AND object_id = OBJECT_ID(N'[dbo].[micu_assessment_values]'))
    CREATE NONCLUSTERED INDEX [IX_micu_assessment_values_micu_assessment_parameter_id] ON [dbo].[micu_assessment_values]([micu_assessment_parameter_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_micu_assessment_values_micu_assessment_id' AND parent_object_id = OBJECT_ID(N'[dbo].[micu_assessment_values]'))
    ALTER TABLE [dbo].[micu_assessment_values] ADD CONSTRAINT [FK_micu_assessment_values_micu_assessment_id] FOREIGN KEY ([micu_assessment_id]) REFERENCES [dbo].[micu_assessments]([id]) ON DELETE CASCADE;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_micu_assessment_values_micu_assessment_parameter_id' AND parent_object_id = OBJECT_ID(N'[dbo].[micu_assessment_values]'))
    ALTER TABLE [dbo].[micu_assessment_values] ADD CONSTRAINT [FK_micu_assessment_values_micu_assessment_parameter_id] FOREIGN KEY ([micu_assessment_parameter_id]) REFERENCES [dbo].[micu_assessment_parameters]([id]) ON DELETE NO ACTION;
-- delivery_types
IF OBJECT_ID(N'[dbo].[delivery_types]', N'U') IS NOT NULL
    DROP TABLE [dbo].[delivery_types];
GO

CREATE TABLE [dbo].[delivery_types] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [description] NVARCHAR(255) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_delivery_types] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_delivery_types_history' AND object_id = OBJECT_ID(N'[dbo].[delivery_types]'))
    CREATE NONCLUSTERED INDEX [IX_delivery_types_history] ON [dbo].[delivery_types]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_delivery_types_is_active' AND object_id = OBJECT_ID(N'[dbo].[delivery_types]'))
    CREATE NONCLUSTERED INDEX [IX_delivery_types_is_active] ON [dbo].[delivery_types]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_delivery_types_created_date' AND object_id = OBJECT_ID(N'[dbo].[delivery_types]'))
    CREATE NONCLUSTERED INDEX [IX_delivery_types_created_date] ON [dbo].[delivery_types]([created_date] ASC);
-- delivery_complications
IF OBJECT_ID(N'[dbo].[delivery_complications]', N'U') IS NOT NULL
    DROP TABLE [dbo].[delivery_complications];
GO

CREATE TABLE [dbo].[delivery_complications] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [description] NVARCHAR(255) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_delivery_complications] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_delivery_complications_history' AND object_id = OBJECT_ID(N'[dbo].[delivery_complications]'))
    CREATE NONCLUSTERED INDEX [IX_delivery_complications_history] ON [dbo].[delivery_complications]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_delivery_complications_is_active' AND object_id = OBJECT_ID(N'[dbo].[delivery_complications]'))
    CREATE NONCLUSTERED INDEX [IX_delivery_complications_is_active] ON [dbo].[delivery_complications]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_delivery_complications_created_date' AND object_id = OBJECT_ID(N'[dbo].[delivery_complications]'))
    CREATE NONCLUSTERED INDEX [IX_delivery_complications_created_date] ON [dbo].[delivery_complications]([created_date] ASC);
-- ward_room_types
IF OBJECT_ID(N'[dbo].[ward_room_types]', N'U') IS NOT NULL
    DROP TABLE [dbo].[ward_room_types];
GO

CREATE TABLE [dbo].[ward_room_types] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [description] NVARCHAR(255) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_ward_room_types] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_ward_room_types_history' AND object_id = OBJECT_ID(N'[dbo].[ward_room_types]'))
    CREATE NONCLUSTERED INDEX [IX_ward_room_types_history] ON [dbo].[ward_room_types]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_ward_room_types_is_active' AND object_id = OBJECT_ID(N'[dbo].[ward_room_types]'))
    CREATE NONCLUSTERED INDEX [IX_ward_room_types_is_active] ON [dbo].[ward_room_types]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_ward_room_types_created_date' AND object_id = OBJECT_ID(N'[dbo].[ward_room_types]'))
    CREATE NONCLUSTERED INDEX [IX_ward_room_types_created_date] ON [dbo].[ward_room_types]([created_date] ASC);
-- wards
IF OBJECT_ID(N'[dbo].[wards]', N'U') IS NOT NULL
    DROP TABLE [dbo].[wards];
GO

CREATE TABLE [dbo].[wards] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [description] NVARCHAR(255) NULL,
    [location] NVARCHAR(255) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_wards] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_wards_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[wards]'))
    ALTER TABLE [dbo].[wards] ADD CONSTRAINT [UQ_wards_code__tenant_id__history] UNIQUE (code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_wards_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[wards]'))
    CREATE NONCLUSTERED INDEX [IX_wards_tenant_id] ON [dbo].[wards]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_wards_history' AND object_id = OBJECT_ID(N'[dbo].[wards]'))
    CREATE NONCLUSTERED INDEX [IX_wards_history] ON [dbo].[wards]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_wards_is_active' AND object_id = OBJECT_ID(N'[dbo].[wards]'))
    CREATE NONCLUSTERED INDEX [IX_wards_is_active] ON [dbo].[wards]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_wards_created_date' AND object_id = OBJECT_ID(N'[dbo].[wards]'))
    CREATE NONCLUSTERED INDEX [IX_wards_created_date] ON [dbo].[wards]([created_date] ASC);
-- ward_rooms
IF OBJECT_ID(N'[dbo].[ward_rooms]', N'U') IS NOT NULL
    DROP TABLE [dbo].[ward_rooms];
GO

CREATE TABLE [dbo].[ward_rooms] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [ward_id] BIGINT NULL,
    [room_type_id] BIGINT NULL,
    [department_id] BIGINT NULL,
    [capacity] INT NOT NULL DEFAULT (1),
    [room_rate] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [extra_bed_rate] DECIMAL(12,2) NULL,
    [discount_rate] DECIMAL(5,2) NULL,
    [status] NVARCHAR(30) NOT NULL DEFAULT (N'Available'),
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_ward_rooms] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_ward_rooms_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[ward_rooms]'))
    ALTER TABLE [dbo].[ward_rooms] ADD CONSTRAINT [UQ_ward_rooms_code__tenant_id__history] UNIQUE (code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_ward_rooms_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[ward_rooms]'))
    CREATE NONCLUSTERED INDEX [IX_ward_rooms_tenant_id] ON [dbo].[ward_rooms]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_ward_rooms_ward_id' AND object_id = OBJECT_ID(N'[dbo].[ward_rooms]'))
    CREATE NONCLUSTERED INDEX [IX_ward_rooms_ward_id] ON [dbo].[ward_rooms]([ward_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_ward_rooms_room_type_id' AND object_id = OBJECT_ID(N'[dbo].[ward_rooms]'))
    CREATE NONCLUSTERED INDEX [IX_ward_rooms_room_type_id] ON [dbo].[ward_rooms]([room_type_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_ward_rooms_department_id' AND object_id = OBJECT_ID(N'[dbo].[ward_rooms]'))
    CREATE NONCLUSTERED INDEX [IX_ward_rooms_department_id] ON [dbo].[ward_rooms]([department_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_ward_rooms_history' AND object_id = OBJECT_ID(N'[dbo].[ward_rooms]'))
    CREATE NONCLUSTERED INDEX [IX_ward_rooms_history] ON [dbo].[ward_rooms]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_ward_rooms_is_active' AND object_id = OBJECT_ID(N'[dbo].[ward_rooms]'))
    CREATE NONCLUSTERED INDEX [IX_ward_rooms_is_active] ON [dbo].[ward_rooms]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_ward_rooms_created_date' AND object_id = OBJECT_ID(N'[dbo].[ward_rooms]'))
    CREATE NONCLUSTERED INDEX [IX_ward_rooms_created_date] ON [dbo].[ward_rooms]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_ward_rooms_status' AND object_id = OBJECT_ID(N'[dbo].[ward_rooms]'))
    CREATE NONCLUSTERED INDEX [IX_ward_rooms_status] ON [dbo].[ward_rooms]([status] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_ward_rooms_ward_id' AND parent_object_id = OBJECT_ID(N'[dbo].[ward_rooms]'))
    ALTER TABLE [dbo].[ward_rooms] ADD CONSTRAINT [FK_ward_rooms_ward_id] FOREIGN KEY ([ward_id]) REFERENCES [dbo].[wards]([id]) ON DELETE SET NULL;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_ward_rooms_room_type_id' AND parent_object_id = OBJECT_ID(N'[dbo].[ward_rooms]'))
    ALTER TABLE [dbo].[ward_rooms] ADD CONSTRAINT [FK_ward_rooms_room_type_id] FOREIGN KEY ([room_type_id]) REFERENCES [dbo].[ward_room_types]([id]) ON DELETE SET NULL;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_ward_rooms_department_id' AND parent_object_id = OBJECT_ID(N'[dbo].[ward_rooms]'))
    ALTER TABLE [dbo].[ward_rooms] ADD CONSTRAINT [FK_ward_rooms_department_id] FOREIGN KEY ([department_id]) REFERENCES [dbo].[departments]([id]) ON DELETE SET NULL;
-- opd_visit_types
IF OBJECT_ID(N'[dbo].[opd_visit_types]', N'U') IS NOT NULL
    DROP TABLE [dbo].[opd_visit_types];
GO

CREATE TABLE [dbo].[opd_visit_types] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [description] NVARCHAR(255) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_opd_visit_types] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_opd_visit_types_history' AND object_id = OBJECT_ID(N'[dbo].[opd_visit_types]'))
    CREATE NONCLUSTERED INDEX [IX_opd_visit_types_history] ON [dbo].[opd_visit_types]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_opd_visit_types_is_active' AND object_id = OBJECT_ID(N'[dbo].[opd_visit_types]'))
    CREATE NONCLUSTERED INDEX [IX_opd_visit_types_is_active] ON [dbo].[opd_visit_types]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_opd_visit_types_created_date' AND object_id = OBJECT_ID(N'[dbo].[opd_visit_types]'))
    CREATE NONCLUSTERED INDEX [IX_opd_visit_types_created_date] ON [dbo].[opd_visit_types]([created_date] ASC);
-- opd_consultation_types
IF OBJECT_ID(N'[dbo].[opd_consultation_types]', N'U') IS NOT NULL
    DROP TABLE [dbo].[opd_consultation_types];
GO

CREATE TABLE [dbo].[opd_consultation_types] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [description] NVARCHAR(255) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_opd_consultation_types] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_opd_consultation_types_history' AND object_id = OBJECT_ID(N'[dbo].[opd_consultation_types]'))
    CREATE NONCLUSTERED INDEX [IX_opd_consultation_types_history] ON [dbo].[opd_consultation_types]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_opd_consultation_types_is_active' AND object_id = OBJECT_ID(N'[dbo].[opd_consultation_types]'))
    CREATE NONCLUSTERED INDEX [IX_opd_consultation_types_is_active] ON [dbo].[opd_consultation_types]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_opd_consultation_types_created_date' AND object_id = OBJECT_ID(N'[dbo].[opd_consultation_types]'))
    CREATE NONCLUSTERED INDEX [IX_opd_consultation_types_created_date] ON [dbo].[opd_consultation_types]([created_date] ASC);
-- opd_referral_sources
IF OBJECT_ID(N'[dbo].[opd_referral_sources]', N'U') IS NOT NULL
    DROP TABLE [dbo].[opd_referral_sources];
GO

CREATE TABLE [dbo].[opd_referral_sources] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [description] NVARCHAR(255) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_opd_referral_sources] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_opd_referral_sources_history' AND object_id = OBJECT_ID(N'[dbo].[opd_referral_sources]'))
    CREATE NONCLUSTERED INDEX [IX_opd_referral_sources_history] ON [dbo].[opd_referral_sources]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_opd_referral_sources_is_active' AND object_id = OBJECT_ID(N'[dbo].[opd_referral_sources]'))
    CREATE NONCLUSTERED INDEX [IX_opd_referral_sources_is_active] ON [dbo].[opd_referral_sources]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_opd_referral_sources_created_date' AND object_id = OBJECT_ID(N'[dbo].[opd_referral_sources]'))
    CREATE NONCLUSTERED INDEX [IX_opd_referral_sources_created_date] ON [dbo].[opd_referral_sources]([created_date] ASC);
-- opd_services
IF OBJECT_ID(N'[dbo].[opd_services]', N'U') IS NOT NULL
    DROP TABLE [dbo].[opd_services];
GO

CREATE TABLE [dbo].[opd_services] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [description] NVARCHAR(255) NULL,
    [standard_charge] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_opd_services] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_opd_services_history' AND object_id = OBJECT_ID(N'[dbo].[opd_services]'))
    CREATE NONCLUSTERED INDEX [IX_opd_services_history] ON [dbo].[opd_services]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_opd_services_is_active' AND object_id = OBJECT_ID(N'[dbo].[opd_services]'))
    CREATE NONCLUSTERED INDEX [IX_opd_services_is_active] ON [dbo].[opd_services]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_opd_services_created_date' AND object_id = OBJECT_ID(N'[dbo].[opd_services]'))
    CREATE NONCLUSTERED INDEX [IX_opd_services_created_date] ON [dbo].[opd_services]([created_date] ASC);
-- opd_clinics
IF OBJECT_ID(N'[dbo].[opd_clinics]', N'U') IS NOT NULL
    DROP TABLE [dbo].[opd_clinics];
GO

CREATE TABLE [dbo].[opd_clinics] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [description] NVARCHAR(255) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_opd_clinics] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_opd_clinics_history' AND object_id = OBJECT_ID(N'[dbo].[opd_clinics]'))
    CREATE NONCLUSTERED INDEX [IX_opd_clinics_history] ON [dbo].[opd_clinics]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_opd_clinics_is_active' AND object_id = OBJECT_ID(N'[dbo].[opd_clinics]'))
    CREATE NONCLUSTERED INDEX [IX_opd_clinics_is_active] ON [dbo].[opd_clinics]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_opd_clinics_created_date' AND object_id = OBJECT_ID(N'[dbo].[opd_clinics]'))
    CREATE NONCLUSTERED INDEX [IX_opd_clinics_created_date] ON [dbo].[opd_clinics]([created_date] ASC);
-- opd_complaints
IF OBJECT_ID(N'[dbo].[opd_complaints]', N'U') IS NOT NULL
    DROP TABLE [dbo].[opd_complaints];
GO

CREATE TABLE [dbo].[opd_complaints] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [description] NVARCHAR(255) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_opd_complaints] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_opd_complaints_history' AND object_id = OBJECT_ID(N'[dbo].[opd_complaints]'))
    CREATE NONCLUSTERED INDEX [IX_opd_complaints_history] ON [dbo].[opd_complaints]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_opd_complaints_is_active' AND object_id = OBJECT_ID(N'[dbo].[opd_complaints]'))
    CREATE NONCLUSTERED INDEX [IX_opd_complaints_is_active] ON [dbo].[opd_complaints]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_opd_complaints_created_date' AND object_id = OBJECT_ID(N'[dbo].[opd_complaints]'))
    CREATE NONCLUSTERED INDEX [IX_opd_complaints_created_date] ON [dbo].[opd_complaints]([created_date] ASC);
-- opd_consultations
IF OBJECT_ID(N'[dbo].[opd_consultations]', N'U') IS NOT NULL
    DROP TABLE [dbo].[opd_consultations];
GO

CREATE TABLE [dbo].[opd_consultations] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [patient_id] BIGINT NOT NULL,
    [consultation_code] NVARCHAR(30) NOT NULL,
    [opd_clinic_id] BIGINT NULL,
    [attending_physician_id] BIGINT NULL,
    [consultation_type_id] BIGINT NULL,
    [visit_type_id] BIGINT NULL,
    [referral_source_id] BIGINT NULL,
    [consultation_datetime] DATETIME2 NOT NULL,
    [status] NVARCHAR(30) NOT NULL DEFAULT (N'Pending'),
    [vital_*] VARIOUS NULL,
    [chief_complaint] NVARCHAR(MAX) NULL,
    [history_of_present_illness] NVARCHAR(MAX) NULL,
    [physical_exam_findings] NVARCHAR(MAX) NULL,
    [assessment] NVARCHAR(MAX) NULL,
    [icd_10_code] NVARCHAR(20) NULL,
    [treatment_plan] NVARCHAR(MAX) NULL,
    [disposition] NVARCHAR(50) NULL,
    [is_active`, `history] TINYINT NOT NULL,
    CONSTRAINT [PK_opd_consultations] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_opd_consultations_consultation_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[opd_consultations]'))
    ALTER TABLE [dbo].[opd_consultations] ADD CONSTRAINT [UQ_opd_consultations_consultation_code__tenant_id__history] UNIQUE (consultation_code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_opd_consultations_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[opd_consultations]'))
    CREATE NONCLUSTERED INDEX [IX_opd_consultations_tenant_id] ON [dbo].[opd_consultations]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_opd_consultations_patient_id' AND object_id = OBJECT_ID(N'[dbo].[opd_consultations]'))
    CREATE NONCLUSTERED INDEX [IX_opd_consultations_patient_id] ON [dbo].[opd_consultations]([patient_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_opd_consultations_opd_clinic_id' AND object_id = OBJECT_ID(N'[dbo].[opd_consultations]'))
    CREATE NONCLUSTERED INDEX [IX_opd_consultations_opd_clinic_id] ON [dbo].[opd_consultations]([opd_clinic_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_opd_consultations_attending_physician_id' AND object_id = OBJECT_ID(N'[dbo].[opd_consultations]'))
    CREATE NONCLUSTERED INDEX [IX_opd_consultations_attending_physician_id] ON [dbo].[opd_consultations]([attending_physician_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_opd_consultations_consultation_type_id' AND object_id = OBJECT_ID(N'[dbo].[opd_consultations]'))
    CREATE NONCLUSTERED INDEX [IX_opd_consultations_consultation_type_id] ON [dbo].[opd_consultations]([consultation_type_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_opd_consultations_visit_type_id' AND object_id = OBJECT_ID(N'[dbo].[opd_consultations]'))
    CREATE NONCLUSTERED INDEX [IX_opd_consultations_visit_type_id] ON [dbo].[opd_consultations]([visit_type_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_opd_consultations_referral_source_id' AND object_id = OBJECT_ID(N'[dbo].[opd_consultations]'))
    CREATE NONCLUSTERED INDEX [IX_opd_consultations_referral_source_id] ON [dbo].[opd_consultations]([referral_source_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_opd_consultations_status' AND object_id = OBJECT_ID(N'[dbo].[opd_consultations]'))
    CREATE NONCLUSTERED INDEX [IX_opd_consultations_status] ON [dbo].[opd_consultations]([status] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_opd_consultations_patient_id' AND parent_object_id = OBJECT_ID(N'[dbo].[opd_consultations]'))
    ALTER TABLE [dbo].[opd_consultations] ADD CONSTRAINT [FK_opd_consultations_patient_id] FOREIGN KEY ([patient_id]) REFERENCES [dbo].[patients]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_opd_consultations_opd_clinic_id' AND parent_object_id = OBJECT_ID(N'[dbo].[opd_consultations]'))
    ALTER TABLE [dbo].[opd_consultations] ADD CONSTRAINT [FK_opd_consultations_opd_clinic_id] FOREIGN KEY ([opd_clinic_id]) REFERENCES [dbo].[opd_clinics]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_opd_consultations_attending_physician_id' AND parent_object_id = OBJECT_ID(N'[dbo].[opd_consultations]'))
    ALTER TABLE [dbo].[opd_consultations] ADD CONSTRAINT [FK_opd_consultations_attending_physician_id] FOREIGN KEY ([attending_physician_id]) REFERENCES [dbo].[hcp]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_opd_consultations_consultation_type_id' AND parent_object_id = OBJECT_ID(N'[dbo].[opd_consultations]'))
    ALTER TABLE [dbo].[opd_consultations] ADD CONSTRAINT [FK_opd_consultations_consultation_type_id] FOREIGN KEY ([consultation_type_id]) REFERENCES [dbo].[opd_consultation_types]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_opd_consultations_visit_type_id' AND parent_object_id = OBJECT_ID(N'[dbo].[opd_consultations]'))
    ALTER TABLE [dbo].[opd_consultations] ADD CONSTRAINT [FK_opd_consultations_visit_type_id] FOREIGN KEY ([visit_type_id]) REFERENCES [dbo].[opd_visit_types]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_opd_consultations_referral_source_id' AND parent_object_id = OBJECT_ID(N'[dbo].[opd_consultations]'))
    ALTER TABLE [dbo].[opd_consultations] ADD CONSTRAINT [FK_opd_consultations_referral_source_id] FOREIGN KEY ([referral_source_id]) REFERENCES [dbo].[opd_referral_sources]([id]) ON DELETE NO ACTION;
-- ob_visit_types
IF OBJECT_ID(N'[dbo].[ob_visit_types]', N'U') IS NOT NULL
    DROP TABLE [dbo].[ob_visit_types];
GO

CREATE TABLE [dbo].[ob_visit_types] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [description] NVARCHAR(255) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_ob_visit_types] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_ob_visit_types_history' AND object_id = OBJECT_ID(N'[dbo].[ob_visit_types]'))
    CREATE NONCLUSTERED INDEX [IX_ob_visit_types_history] ON [dbo].[ob_visit_types]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_ob_visit_types_is_active' AND object_id = OBJECT_ID(N'[dbo].[ob_visit_types]'))
    CREATE NONCLUSTERED INDEX [IX_ob_visit_types_is_active] ON [dbo].[ob_visit_types]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_ob_visit_types_created_date' AND object_id = OBJECT_ID(N'[dbo].[ob_visit_types]'))
    CREATE NONCLUSTERED INDEX [IX_ob_visit_types_created_date] ON [dbo].[ob_visit_types]([created_date] ASC);
-- ob_fetal_presentations
IF OBJECT_ID(N'[dbo].[ob_fetal_presentations]', N'U') IS NOT NULL
    DROP TABLE [dbo].[ob_fetal_presentations];
GO

CREATE TABLE [dbo].[ob_fetal_presentations] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [description] NVARCHAR(255) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_ob_fetal_presentations] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_ob_fetal_presentations_history' AND object_id = OBJECT_ID(N'[dbo].[ob_fetal_presentations]'))
    CREATE NONCLUSTERED INDEX [IX_ob_fetal_presentations_history] ON [dbo].[ob_fetal_presentations]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_ob_fetal_presentations_is_active' AND object_id = OBJECT_ID(N'[dbo].[ob_fetal_presentations]'))
    CREATE NONCLUSTERED INDEX [IX_ob_fetal_presentations_is_active] ON [dbo].[ob_fetal_presentations]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_ob_fetal_presentations_created_date' AND object_id = OBJECT_ID(N'[dbo].[ob_fetal_presentations]'))
    CREATE NONCLUSTERED INDEX [IX_ob_fetal_presentations_created_date] ON [dbo].[ob_fetal_presentations]([created_date] ASC);
-- ob_contraceptive_methods
IF OBJECT_ID(N'[dbo].[ob_contraceptive_methods]', N'U') IS NOT NULL
    DROP TABLE [dbo].[ob_contraceptive_methods];
GO

CREATE TABLE [dbo].[ob_contraceptive_methods] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [description] NVARCHAR(255) NULL,
    [category] NVARCHAR(50) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_ob_contraceptive_methods] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_ob_contraceptive_methods_history' AND object_id = OBJECT_ID(N'[dbo].[ob_contraceptive_methods]'))
    CREATE NONCLUSTERED INDEX [IX_ob_contraceptive_methods_history] ON [dbo].[ob_contraceptive_methods]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_ob_contraceptive_methods_is_active' AND object_id = OBJECT_ID(N'[dbo].[ob_contraceptive_methods]'))
    CREATE NONCLUSTERED INDEX [IX_ob_contraceptive_methods_is_active] ON [dbo].[ob_contraceptive_methods]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_ob_contraceptive_methods_created_date' AND object_id = OBJECT_ID(N'[dbo].[ob_contraceptive_methods]'))
    CREATE NONCLUSTERED INDEX [IX_ob_contraceptive_methods_created_date] ON [dbo].[ob_contraceptive_methods]([created_date] ASC);
-- ob_consultations
IF OBJECT_ID(N'[dbo].[ob_consultations]', N'U') IS NOT NULL
    DROP TABLE [dbo].[ob_consultations];
GO

CREATE TABLE [dbo].[ob_consultations] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [consultation_code] NVARCHAR(30) NOT NULL,
    [patient_id] BIGINT NOT NULL,
    [attending_physician_id] BIGINT NULL,
    [visit_type_id] BIGINT NULL,
    [gestational_age] NVARCHAR(20) NULL,
    [lmp_date] DATE NULL,
    [edc_date] DATE NULL,
    [gravida] TINYINT NULL,
    [para] TINYINT NULL,
    [full_term] TINYINT NULL,
    [preterm] TINYINT NULL,
    [abortion] TINYINT NULL,
    [living_children] TINYINT NULL,
    [obstetrical_history] NVARCHAR(MAX) NULL,
    [history_of_present_illness] NVARCHAR(MAX) NULL,
    [physical_exam_findings] NVARCHAR(MAX) NULL,
    [assessment] NVARCHAR(MAX) NULL,
    [icd_10_code] NVARCHAR(20) NULL,
    [orders_summary] NVARCHAR(MAX) NULL,
    [treatment_notes] NVARCHAR(MAX) NULL,
    [disposition] NVARCHAR(30) NOT NULL DEFAULT (N'Pending'),
    [discharge_instructions] NVARCHAR(MAX) NULL,
    [disposition_datetime] DATETIME2 NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_ob_consultations] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_ob_consultations_consultation_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[ob_consultations]'))
    ALTER TABLE [dbo].[ob_consultations] ADD CONSTRAINT [UQ_ob_consultations_consultation_code__tenant_id__history] UNIQUE (consultation_code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_ob_consultations_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[ob_consultations]'))
    CREATE NONCLUSTERED INDEX [IX_ob_consultations_tenant_id] ON [dbo].[ob_consultations]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_ob_consultations_patient_id' AND object_id = OBJECT_ID(N'[dbo].[ob_consultations]'))
    CREATE NONCLUSTERED INDEX [IX_ob_consultations_patient_id] ON [dbo].[ob_consultations]([patient_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_ob_consultations_attending_physician_id' AND object_id = OBJECT_ID(N'[dbo].[ob_consultations]'))
    CREATE NONCLUSTERED INDEX [IX_ob_consultations_attending_physician_id] ON [dbo].[ob_consultations]([attending_physician_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_ob_consultations_visit_type_id' AND object_id = OBJECT_ID(N'[dbo].[ob_consultations]'))
    CREATE NONCLUSTERED INDEX [IX_ob_consultations_visit_type_id] ON [dbo].[ob_consultations]([visit_type_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_ob_consultations_history' AND object_id = OBJECT_ID(N'[dbo].[ob_consultations]'))
    CREATE NONCLUSTERED INDEX [IX_ob_consultations_history] ON [dbo].[ob_consultations]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_ob_consultations_is_active' AND object_id = OBJECT_ID(N'[dbo].[ob_consultations]'))
    CREATE NONCLUSTERED INDEX [IX_ob_consultations_is_active] ON [dbo].[ob_consultations]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_ob_consultations_created_date' AND object_id = OBJECT_ID(N'[dbo].[ob_consultations]'))
    CREATE NONCLUSTERED INDEX [IX_ob_consultations_created_date] ON [dbo].[ob_consultations]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_ob_consultations_patient_id' AND parent_object_id = OBJECT_ID(N'[dbo].[ob_consultations]'))
    ALTER TABLE [dbo].[ob_consultations] ADD CONSTRAINT [FK_ob_consultations_patient_id] FOREIGN KEY ([patient_id]) REFERENCES [dbo].[patients]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_ob_consultations_attending_physician_id' AND parent_object_id = OBJECT_ID(N'[dbo].[ob_consultations]'))
    ALTER TABLE [dbo].[ob_consultations] ADD CONSTRAINT [FK_ob_consultations_attending_physician_id] FOREIGN KEY ([attending_physician_id]) REFERENCES [dbo].[health_care_professionals]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_ob_consultations_visit_type_id' AND parent_object_id = OBJECT_ID(N'[dbo].[ob_consultations]'))
    ALTER TABLE [dbo].[ob_consultations] ADD CONSTRAINT [FK_ob_consultations_visit_type_id] FOREIGN KEY ([visit_type_id]) REFERENCES [dbo].[ob_visit_types]([id]) ON DELETE SET NULL;
-- er_acuity_levels
IF OBJECT_ID(N'[dbo].[er_acuity_levels]', N'U') IS NOT NULL
    DROP TABLE [dbo].[er_acuity_levels];
GO

CREATE TABLE [dbo].[er_acuity_levels] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [description] NVARCHAR(255) NULL,
    [color_code] NVARCHAR(20) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_er_acuity_levels] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_er_acuity_levels_history' AND object_id = OBJECT_ID(N'[dbo].[er_acuity_levels]'))
    CREATE NONCLUSTERED INDEX [IX_er_acuity_levels_history] ON [dbo].[er_acuity_levels]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_er_acuity_levels_is_active' AND object_id = OBJECT_ID(N'[dbo].[er_acuity_levels]'))
    CREATE NONCLUSTERED INDEX [IX_er_acuity_levels_is_active] ON [dbo].[er_acuity_levels]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_er_acuity_levels_created_date' AND object_id = OBJECT_ID(N'[dbo].[er_acuity_levels]'))
    CREATE NONCLUSTERED INDEX [IX_er_acuity_levels_created_date] ON [dbo].[er_acuity_levels]([created_date] ASC);
-- er_mobility_statuses
IF OBJECT_ID(N'[dbo].[er_mobility_statuses]', N'U') IS NOT NULL
    DROP TABLE [dbo].[er_mobility_statuses];
GO

CREATE TABLE [dbo].[er_mobility_statuses] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [description] NVARCHAR(255) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_er_mobility_statuses] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_er_mobility_statuses_history' AND object_id = OBJECT_ID(N'[dbo].[er_mobility_statuses]'))
    CREATE NONCLUSTERED INDEX [IX_er_mobility_statuses_history] ON [dbo].[er_mobility_statuses]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_er_mobility_statuses_is_active' AND object_id = OBJECT_ID(N'[dbo].[er_mobility_statuses]'))
    CREATE NONCLUSTERED INDEX [IX_er_mobility_statuses_is_active] ON [dbo].[er_mobility_statuses]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_er_mobility_statuses_created_date' AND object_id = OBJECT_ID(N'[dbo].[er_mobility_statuses]'))
    CREATE NONCLUSTERED INDEX [IX_er_mobility_statuses_created_date] ON [dbo].[er_mobility_statuses]([created_date] ASC);
-- er_zones
IF OBJECT_ID(N'[dbo].[er_zones]', N'U') IS NOT NULL
    DROP TABLE [dbo].[er_zones];
GO

CREATE TABLE [dbo].[er_zones] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [description] NVARCHAR(255) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_er_zones] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_er_zones_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[er_zones]'))
    ALTER TABLE [dbo].[er_zones] ADD CONSTRAINT [UQ_er_zones_code__tenant_id__history] UNIQUE (code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_er_zones_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[er_zones]'))
    CREATE NONCLUSTERED INDEX [IX_er_zones_tenant_id] ON [dbo].[er_zones]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_er_zones_history' AND object_id = OBJECT_ID(N'[dbo].[er_zones]'))
    CREATE NONCLUSTERED INDEX [IX_er_zones_history] ON [dbo].[er_zones]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_er_zones_is_active' AND object_id = OBJECT_ID(N'[dbo].[er_zones]'))
    CREATE NONCLUSTERED INDEX [IX_er_zones_is_active] ON [dbo].[er_zones]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_er_zones_created_date' AND object_id = OBJECT_ID(N'[dbo].[er_zones]'))
    CREATE NONCLUSTERED INDEX [IX_er_zones_created_date] ON [dbo].[er_zones]([created_date] ASC);
-- er_beds
IF OBJECT_ID(N'[dbo].[er_beds]', N'U') IS NOT NULL
    DROP TABLE [dbo].[er_beds];
GO

CREATE TABLE [dbo].[er_beds] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [er_zone_id] BIGINT NULL,
    [status] NVARCHAR(30) NOT NULL DEFAULT (N'Available'),
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_er_beds] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_er_beds_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[er_beds]'))
    ALTER TABLE [dbo].[er_beds] ADD CONSTRAINT [UQ_er_beds_code__tenant_id__history] UNIQUE (code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_er_beds_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[er_beds]'))
    CREATE NONCLUSTERED INDEX [IX_er_beds_tenant_id] ON [dbo].[er_beds]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_er_beds_er_zone_id' AND object_id = OBJECT_ID(N'[dbo].[er_beds]'))
    CREATE NONCLUSTERED INDEX [IX_er_beds_er_zone_id] ON [dbo].[er_beds]([er_zone_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_er_beds_history' AND object_id = OBJECT_ID(N'[dbo].[er_beds]'))
    CREATE NONCLUSTERED INDEX [IX_er_beds_history] ON [dbo].[er_beds]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_er_beds_is_active' AND object_id = OBJECT_ID(N'[dbo].[er_beds]'))
    CREATE NONCLUSTERED INDEX [IX_er_beds_is_active] ON [dbo].[er_beds]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_er_beds_created_date' AND object_id = OBJECT_ID(N'[dbo].[er_beds]'))
    CREATE NONCLUSTERED INDEX [IX_er_beds_created_date] ON [dbo].[er_beds]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_er_beds_status' AND object_id = OBJECT_ID(N'[dbo].[er_beds]'))
    CREATE NONCLUSTERED INDEX [IX_er_beds_status] ON [dbo].[er_beds]([status] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_er_beds_er_zone_id' AND parent_object_id = OBJECT_ID(N'[dbo].[er_beds]'))
    ALTER TABLE [dbo].[er_beds] ADD CONSTRAINT [FK_er_beds_er_zone_id] FOREIGN KEY ([er_zone_id]) REFERENCES [dbo].[er_zones]([id]) ON DELETE NO ACTION;
-- er_triage_records
IF OBJECT_ID(N'[dbo].[er_triage_records]', N'U') IS NOT NULL
    DROP TABLE [dbo].[er_triage_records];
GO

CREATE TABLE [dbo].[er_triage_records] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [triage_code] NVARCHAR(30) NOT NULL,
    [patient_id] BIGINT NULL,
    [er_acuity_level_id] BIGINT NULL,
    [er_mobility_status_id] BIGINT NULL,
    [triage_datetime] DATETIME2 NOT NULL,
    [chief_complaint] NVARCHAR(MAX) NOT NULL,
    [vital_temperature] DECIMAL(5,2) NULL,
    [vital_heart_rate] INT NULL,
    [vital_respiratory_rate] INT NULL,
    [vital_bp_systolic] INT NULL,
    [vital_bp_diastolic] INT NULL,
    [vital_o2_saturation] DECIMAL(5,2) NULL,
    [vital_pain_score] INT NULL,
    [level_of_consciousness] NVARCHAR(50) NULL,
    [allergies] NVARCHAR(MAX) NULL,
    [notes] NVARCHAR(MAX) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_er_triage_records] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_er_triage_records_triage_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[er_triage_records]'))
    ALTER TABLE [dbo].[er_triage_records] ADD CONSTRAINT [UQ_er_triage_records_triage_code__tenant_id__history] UNIQUE (triage_code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_er_triage_records_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[er_triage_records]'))
    CREATE NONCLUSTERED INDEX [IX_er_triage_records_tenant_id] ON [dbo].[er_triage_records]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_er_triage_records_patient_id' AND object_id = OBJECT_ID(N'[dbo].[er_triage_records]'))
    CREATE NONCLUSTERED INDEX [IX_er_triage_records_patient_id] ON [dbo].[er_triage_records]([patient_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_er_triage_records_er_acuity_level_id' AND object_id = OBJECT_ID(N'[dbo].[er_triage_records]'))
    CREATE NONCLUSTERED INDEX [IX_er_triage_records_er_acuity_level_id] ON [dbo].[er_triage_records]([er_acuity_level_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_er_triage_records_er_mobility_status_id' AND object_id = OBJECT_ID(N'[dbo].[er_triage_records]'))
    CREATE NONCLUSTERED INDEX [IX_er_triage_records_er_mobility_status_id] ON [dbo].[er_triage_records]([er_mobility_status_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_er_triage_records_history' AND object_id = OBJECT_ID(N'[dbo].[er_triage_records]'))
    CREATE NONCLUSTERED INDEX [IX_er_triage_records_history] ON [dbo].[er_triage_records]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_er_triage_records_is_active' AND object_id = OBJECT_ID(N'[dbo].[er_triage_records]'))
    CREATE NONCLUSTERED INDEX [IX_er_triage_records_is_active] ON [dbo].[er_triage_records]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_er_triage_records_created_date' AND object_id = OBJECT_ID(N'[dbo].[er_triage_records]'))
    CREATE NONCLUSTERED INDEX [IX_er_triage_records_created_date] ON [dbo].[er_triage_records]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_er_triage_records_patient_id' AND parent_object_id = OBJECT_ID(N'[dbo].[er_triage_records]'))
    ALTER TABLE [dbo].[er_triage_records] ADD CONSTRAINT [FK_er_triage_records_patient_id] FOREIGN KEY ([patient_id]) REFERENCES [dbo].[patients]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_er_triage_records_er_acuity_level_id' AND parent_object_id = OBJECT_ID(N'[dbo].[er_triage_records]'))
    ALTER TABLE [dbo].[er_triage_records] ADD CONSTRAINT [FK_er_triage_records_er_acuity_level_id] FOREIGN KEY ([er_acuity_level_id]) REFERENCES [dbo].[er_acuity_levels]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_er_triage_records_er_mobility_status_id' AND parent_object_id = OBJECT_ID(N'[dbo].[er_triage_records]'))
    ALTER TABLE [dbo].[er_triage_records] ADD CONSTRAINT [FK_er_triage_records_er_mobility_status_id] FOREIGN KEY ([er_mobility_status_id]) REFERENCES [dbo].[er_mobility_statuses]([id]) ON DELETE NO ACTION;
-- er_admissions
IF OBJECT_ID(N'[dbo].[er_admissions]', N'U') IS NOT NULL
    DROP TABLE [dbo].[er_admissions];
GO

CREATE TABLE [dbo].[er_admissions] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [admission_code] NVARCHAR(30) NOT NULL,
    [er_triage_record_id] BIGINT NULL,
    [patient_id] BIGINT NOT NULL,
    [er_bed_id] BIGINT NULL,
    [attending_physician_id] BIGINT NULL,
    [arrival_mode] NVARCHAR(50) NULL,
    [admission_datetime] DATETIME2 NOT NULL,
    [initial_diagnosis] NVARCHAR(MAX) NULL,
    [history_of_present_illness] NVARCHAR(MAX) NULL,
    [physical_exam_findings] NVARCHAR(MAX) NULL,
    [assessment] NVARCHAR(MAX) NULL,
    [icd_10_code] NVARCHAR(20) NULL,
    [orders_summary] NVARCHAR(MAX) NULL,
    [treatment_notes] NVARCHAR(MAX) NULL,
    [disposition] NVARCHAR(50) NOT NULL DEFAULT (N'Pending'),
    [disposition_datetime] DATETIME2 NULL,
    [discharge_instructions] NVARCHAR(MAX) NULL,
    [is_oecb] TINYINT NOT NULL DEFAULT (0),
    [billing_id] BIGINT NULL,
    [billing_status] NVARCHAR(30) NOT NULL DEFAULT (N'Pending'),
    [status] NVARCHAR(30) NOT NULL DEFAULT (N'Active'),
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_er_admissions] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_er_admissions_admission_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[er_admissions]'))
    ALTER TABLE [dbo].[er_admissions] ADD CONSTRAINT [UQ_er_admissions_admission_code__tenant_id__history] UNIQUE (admission_code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_er_admissions_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[er_admissions]'))
    CREATE NONCLUSTERED INDEX [IX_er_admissions_tenant_id] ON [dbo].[er_admissions]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_er_admissions_er_triage_record_id' AND object_id = OBJECT_ID(N'[dbo].[er_admissions]'))
    CREATE NONCLUSTERED INDEX [IX_er_admissions_er_triage_record_id] ON [dbo].[er_admissions]([er_triage_record_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_er_admissions_patient_id' AND object_id = OBJECT_ID(N'[dbo].[er_admissions]'))
    CREATE NONCLUSTERED INDEX [IX_er_admissions_patient_id] ON [dbo].[er_admissions]([patient_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_er_admissions_er_bed_id' AND object_id = OBJECT_ID(N'[dbo].[er_admissions]'))
    CREATE NONCLUSTERED INDEX [IX_er_admissions_er_bed_id] ON [dbo].[er_admissions]([er_bed_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_er_admissions_attending_physician_id' AND object_id = OBJECT_ID(N'[dbo].[er_admissions]'))
    CREATE NONCLUSTERED INDEX [IX_er_admissions_attending_physician_id] ON [dbo].[er_admissions]([attending_physician_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_er_admissions_history' AND object_id = OBJECT_ID(N'[dbo].[er_admissions]'))
    CREATE NONCLUSTERED INDEX [IX_er_admissions_history] ON [dbo].[er_admissions]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_er_admissions_is_active' AND object_id = OBJECT_ID(N'[dbo].[er_admissions]'))
    CREATE NONCLUSTERED INDEX [IX_er_admissions_is_active] ON [dbo].[er_admissions]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_er_admissions_created_date' AND object_id = OBJECT_ID(N'[dbo].[er_admissions]'))
    CREATE NONCLUSTERED INDEX [IX_er_admissions_created_date] ON [dbo].[er_admissions]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_er_admissions_status' AND object_id = OBJECT_ID(N'[dbo].[er_admissions]'))
    CREATE NONCLUSTERED INDEX [IX_er_admissions_status] ON [dbo].[er_admissions]([status] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_er_admissions_er_triage_record_id' AND parent_object_id = OBJECT_ID(N'[dbo].[er_admissions]'))
    ALTER TABLE [dbo].[er_admissions] ADD CONSTRAINT [FK_er_admissions_er_triage_record_id] FOREIGN KEY ([er_triage_record_id]) REFERENCES [dbo].[er_triage_records]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_er_admissions_patient_id' AND parent_object_id = OBJECT_ID(N'[dbo].[er_admissions]'))
    ALTER TABLE [dbo].[er_admissions] ADD CONSTRAINT [FK_er_admissions_patient_id] FOREIGN KEY ([patient_id]) REFERENCES [dbo].[patients]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_er_admissions_er_bed_id' AND parent_object_id = OBJECT_ID(N'[dbo].[er_admissions]'))
    ALTER TABLE [dbo].[er_admissions] ADD CONSTRAINT [FK_er_admissions_er_bed_id] FOREIGN KEY ([er_bed_id]) REFERENCES [dbo].[er_beds]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_er_admissions_attending_physician_id' AND parent_object_id = OBJECT_ID(N'[dbo].[er_admissions]'))
    ALTER TABLE [dbo].[er_admissions] ADD CONSTRAINT [FK_er_admissions_attending_physician_id] FOREIGN KEY ([attending_physician_id]) REFERENCES [dbo].[health_care_professionals]([id]) ON DELETE NO ACTION;
-- admissions
IF OBJECT_ID(N'[dbo].[admissions]', N'U') IS NOT NULL
    DROP TABLE [dbo].[admissions];
GO

CREATE TABLE [dbo].[admissions] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [admission_code] NVARCHAR(30) NOT NULL,
    [patient_id] BIGINT NOT NULL,
    [er_admission_id] BIGINT NULL,
    [admission_type] NVARCHAR(30) NOT NULL,
    [admission_source] NVARCHAR(30) NOT NULL,
    [admission_date] DATETIME2 NOT NULL,
    [admitting_diagnosis] NVARCHAR(MAX) NULL,
    [attending_physician_id] BIGINT NULL,
    [admitting_physician_id] BIGINT NULL,
    [ward_room_id] BIGINT NULL,
    [icu_bed_id] BIGINT NULL,
    [status] NVARCHAR(30) NOT NULL DEFAULT (N'Admitted'),
    [billing_id] BIGINT NULL,
    [billing_status] NVARCHAR(50) NULL DEFAULT (N'Pending'),
    [discharge_date] DATETIME2 NULL,
    [notes] NVARCHAR(MAX) NULL,
    [history_of_present_illness] NVARCHAR(MAX) NULL,
    [physical_exam_findings] NVARCHAR(MAX) NULL,
    [assessment] NVARCHAR(MAX) NULL,
    [icd_10_code] NVARCHAR(20) NULL,
    [orders_summary] NVARCHAR(MAX) NULL,
    [treatment_notes] NVARCHAR(MAX) NULL,
    [disposition] NVARCHAR(30) NOT NULL DEFAULT (N'Pending'),
    [discharge_instructions] NVARCHAR(MAX) NULL,
    [disposition_datetime] DATETIME2 NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_admissions] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_admissions_admission_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[admissions]'))
    ALTER TABLE [dbo].[admissions] ADD CONSTRAINT [UQ_admissions_admission_code__tenant_id__history] UNIQUE (admission_code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_admissions_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[admissions]'))
    CREATE NONCLUSTERED INDEX [IX_admissions_tenant_id] ON [dbo].[admissions]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_admissions_patient_id' AND object_id = OBJECT_ID(N'[dbo].[admissions]'))
    CREATE NONCLUSTERED INDEX [IX_admissions_patient_id] ON [dbo].[admissions]([patient_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_admissions_er_admission_id' AND object_id = OBJECT_ID(N'[dbo].[admissions]'))
    CREATE NONCLUSTERED INDEX [IX_admissions_er_admission_id] ON [dbo].[admissions]([er_admission_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_admissions_attending_physician_id' AND object_id = OBJECT_ID(N'[dbo].[admissions]'))
    CREATE NONCLUSTERED INDEX [IX_admissions_attending_physician_id] ON [dbo].[admissions]([attending_physician_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_admissions_admitting_physician_id' AND object_id = OBJECT_ID(N'[dbo].[admissions]'))
    CREATE NONCLUSTERED INDEX [IX_admissions_admitting_physician_id] ON [dbo].[admissions]([admitting_physician_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_admissions_ward_room_id' AND object_id = OBJECT_ID(N'[dbo].[admissions]'))
    CREATE NONCLUSTERED INDEX [IX_admissions_ward_room_id] ON [dbo].[admissions]([ward_room_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_admissions_icu_bed_id' AND object_id = OBJECT_ID(N'[dbo].[admissions]'))
    CREATE NONCLUSTERED INDEX [IX_admissions_icu_bed_id] ON [dbo].[admissions]([icu_bed_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_admissions_history' AND object_id = OBJECT_ID(N'[dbo].[admissions]'))
    CREATE NONCLUSTERED INDEX [IX_admissions_history] ON [dbo].[admissions]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_admissions_is_active' AND object_id = OBJECT_ID(N'[dbo].[admissions]'))
    CREATE NONCLUSTERED INDEX [IX_admissions_is_active] ON [dbo].[admissions]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_admissions_created_date' AND object_id = OBJECT_ID(N'[dbo].[admissions]'))
    CREATE NONCLUSTERED INDEX [IX_admissions_created_date] ON [dbo].[admissions]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_admissions_status' AND object_id = OBJECT_ID(N'[dbo].[admissions]'))
    CREATE NONCLUSTERED INDEX [IX_admissions_status] ON [dbo].[admissions]([status] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_admissions_patient_id' AND parent_object_id = OBJECT_ID(N'[dbo].[admissions]'))
    ALTER TABLE [dbo].[admissions] ADD CONSTRAINT [FK_admissions_patient_id] FOREIGN KEY ([patient_id]) REFERENCES [dbo].[patients]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_admissions_er_admission_id' AND parent_object_id = OBJECT_ID(N'[dbo].[admissions]'))
    ALTER TABLE [dbo].[admissions] ADD CONSTRAINT [FK_admissions_er_admission_id] FOREIGN KEY ([er_admission_id]) REFERENCES [dbo].[er_admissions]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_admissions_attending_physician_id' AND parent_object_id = OBJECT_ID(N'[dbo].[admissions]'))
    ALTER TABLE [dbo].[admissions] ADD CONSTRAINT [FK_admissions_attending_physician_id] FOREIGN KEY ([attending_physician_id]) REFERENCES [dbo].[health_care_professionals]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_admissions_admitting_physician_id' AND parent_object_id = OBJECT_ID(N'[dbo].[admissions]'))
    ALTER TABLE [dbo].[admissions] ADD CONSTRAINT [FK_admissions_admitting_physician_id] FOREIGN KEY ([admitting_physician_id]) REFERENCES [dbo].[health_care_professionals]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_admissions_ward_room_id' AND parent_object_id = OBJECT_ID(N'[dbo].[admissions]'))
    ALTER TABLE [dbo].[admissions] ADD CONSTRAINT [FK_admissions_ward_room_id] FOREIGN KEY ([ward_room_id]) REFERENCES [dbo].[ward_rooms]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_admissions_icu_bed_id' AND parent_object_id = OBJECT_ID(N'[dbo].[admissions]'))
    ALTER TABLE [dbo].[admissions] ADD CONSTRAINT [FK_admissions_icu_bed_id] FOREIGN KEY ([icu_bed_id]) REFERENCES [dbo].[icu_beds]([id]) ON DELETE NO ACTION;
-- or_rooms
IF OBJECT_ID(N'[dbo].[or_rooms]', N'U') IS NOT NULL
    DROP TABLE [dbo].[or_rooms];
GO

CREATE TABLE [dbo].[or_rooms] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [location] NVARCHAR(100) NULL,
    [room_type] NVARCHAR(50) NULL,
    [capacity] INT NOT NULL DEFAULT (1),
    [equipment] NVARCHAR(MAX) NULL,
    [special_requirements] NVARCHAR(MAX) NULL,
    [status] NVARCHAR(30) NOT NULL DEFAULT (N'Available'),
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_or_rooms] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_or_rooms_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[or_rooms]'))
    ALTER TABLE [dbo].[or_rooms] ADD CONSTRAINT [UQ_or_rooms_code__tenant_id__history] UNIQUE (code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_or_rooms_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[or_rooms]'))
    CREATE NONCLUSTERED INDEX [IX_or_rooms_tenant_id] ON [dbo].[or_rooms]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_or_rooms_history' AND object_id = OBJECT_ID(N'[dbo].[or_rooms]'))
    CREATE NONCLUSTERED INDEX [IX_or_rooms_history] ON [dbo].[or_rooms]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_or_rooms_is_active' AND object_id = OBJECT_ID(N'[dbo].[or_rooms]'))
    CREATE NONCLUSTERED INDEX [IX_or_rooms_is_active] ON [dbo].[or_rooms]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_or_rooms_created_date' AND object_id = OBJECT_ID(N'[dbo].[or_rooms]'))
    CREATE NONCLUSTERED INDEX [IX_or_rooms_created_date] ON [dbo].[or_rooms]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_or_rooms_status' AND object_id = OBJECT_ID(N'[dbo].[or_rooms]'))
    CREATE NONCLUSTERED INDEX [IX_or_rooms_status] ON [dbo].[or_rooms]([status] ASC);
-- or_schedules
IF OBJECT_ID(N'[dbo].[or_schedules]', N'U') IS NOT NULL
    DROP TABLE [dbo].[or_schedules];
GO

CREATE TABLE [dbo].[or_schedules] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [schedule_code] NVARCHAR(30) NOT NULL,
    [or_room_id] BIGINT NOT NULL,
    [patient_id] BIGINT NOT NULL,
    [er_admission_id] BIGINT NULL,
    [admission_id] BIGINT NULL,
    [ob_consultation_id] BIGINT NULL,
    [surgeon_id] BIGINT NULL,
    [anesthesiologist_id] BIGINT NULL,
    [procedure_type] NVARCHAR(100) NULL,
    [procedure_description] NVARCHAR(MAX) NULL,
    [scheduled_date] DATE NOT NULL,
    [start_time] TIME NULL,
    [end_time] TIME NULL,
    [status] NVARCHAR(30) NOT NULL DEFAULT (N'Scheduled'),
    [pre_op_notes] NVARCHAR(MAX) NULL,
    [intra_op_notes] NVARCHAR(MAX) NULL,
    [post_op_notes] NVARCHAR(MAX) NULL,
    [cancellation_reason] NVARCHAR(MAX) NULL,
    [history_of_present_illness] NVARCHAR(MAX) NULL,
    [physical_exam_findings] NVARCHAR(MAX) NULL,
    [assessment] NVARCHAR(MAX) NULL,
    [orders_summary] NVARCHAR(MAX) NULL,
    [treatment_notes] NVARCHAR(MAX) NULL,
    [disposition] NVARCHAR(30) NOT NULL DEFAULT (N'Pending'),
    [discharge_instructions] NVARCHAR(MAX) NULL,
    [disposition_datetime] DATETIME2 NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_or_schedules] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_or_schedules_schedule_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[or_schedules]'))
    ALTER TABLE [dbo].[or_schedules] ADD CONSTRAINT [UQ_or_schedules_schedule_code__tenant_id__history] UNIQUE (schedule_code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_or_schedules_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[or_schedules]'))
    CREATE NONCLUSTERED INDEX [IX_or_schedules_tenant_id] ON [dbo].[or_schedules]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_or_schedules_or_room_id' AND object_id = OBJECT_ID(N'[dbo].[or_schedules]'))
    CREATE NONCLUSTERED INDEX [IX_or_schedules_or_room_id] ON [dbo].[or_schedules]([or_room_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_or_schedules_patient_id' AND object_id = OBJECT_ID(N'[dbo].[or_schedules]'))
    CREATE NONCLUSTERED INDEX [IX_or_schedules_patient_id] ON [dbo].[or_schedules]([patient_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_or_schedules_er_admission_id' AND object_id = OBJECT_ID(N'[dbo].[or_schedules]'))
    CREATE NONCLUSTERED INDEX [IX_or_schedules_er_admission_id] ON [dbo].[or_schedules]([er_admission_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_or_schedules_admission_id' AND object_id = OBJECT_ID(N'[dbo].[or_schedules]'))
    CREATE NONCLUSTERED INDEX [IX_or_schedules_admission_id] ON [dbo].[or_schedules]([admission_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_or_schedules_ob_consultation_id' AND object_id = OBJECT_ID(N'[dbo].[or_schedules]'))
    CREATE NONCLUSTERED INDEX [IX_or_schedules_ob_consultation_id] ON [dbo].[or_schedules]([ob_consultation_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_or_schedules_surgeon_id' AND object_id = OBJECT_ID(N'[dbo].[or_schedules]'))
    CREATE NONCLUSTERED INDEX [IX_or_schedules_surgeon_id] ON [dbo].[or_schedules]([surgeon_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_or_schedules_anesthesiologist_id' AND object_id = OBJECT_ID(N'[dbo].[or_schedules]'))
    CREATE NONCLUSTERED INDEX [IX_or_schedules_anesthesiologist_id] ON [dbo].[or_schedules]([anesthesiologist_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_or_schedules_history' AND object_id = OBJECT_ID(N'[dbo].[or_schedules]'))
    CREATE NONCLUSTERED INDEX [IX_or_schedules_history] ON [dbo].[or_schedules]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_or_schedules_is_active' AND object_id = OBJECT_ID(N'[dbo].[or_schedules]'))
    CREATE NONCLUSTERED INDEX [IX_or_schedules_is_active] ON [dbo].[or_schedules]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_or_schedules_created_date' AND object_id = OBJECT_ID(N'[dbo].[or_schedules]'))
    CREATE NONCLUSTERED INDEX [IX_or_schedules_created_date] ON [dbo].[or_schedules]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_or_schedules_status' AND object_id = OBJECT_ID(N'[dbo].[or_schedules]'))
    CREATE NONCLUSTERED INDEX [IX_or_schedules_status] ON [dbo].[or_schedules]([status] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_or_schedules_or_room_id' AND parent_object_id = OBJECT_ID(N'[dbo].[or_schedules]'))
    ALTER TABLE [dbo].[or_schedules] ADD CONSTRAINT [FK_or_schedules_or_room_id] FOREIGN KEY ([or_room_id]) REFERENCES [dbo].[or_rooms]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_or_schedules_patient_id' AND parent_object_id = OBJECT_ID(N'[dbo].[or_schedules]'))
    ALTER TABLE [dbo].[or_schedules] ADD CONSTRAINT [FK_or_schedules_patient_id] FOREIGN KEY ([patient_id]) REFERENCES [dbo].[patients]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_or_schedules_er_admission_id' AND parent_object_id = OBJECT_ID(N'[dbo].[or_schedules]'))
    ALTER TABLE [dbo].[or_schedules] ADD CONSTRAINT [FK_or_schedules_er_admission_id] FOREIGN KEY ([er_admission_id]) REFERENCES [dbo].[er_admissions]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_or_schedules_admission_id' AND parent_object_id = OBJECT_ID(N'[dbo].[or_schedules]'))
    ALTER TABLE [dbo].[or_schedules] ADD CONSTRAINT [FK_or_schedules_admission_id] FOREIGN KEY ([admission_id]) REFERENCES [dbo].[admissions]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_or_schedules_ob_consultation_id' AND parent_object_id = OBJECT_ID(N'[dbo].[or_schedules]'))
    ALTER TABLE [dbo].[or_schedules] ADD CONSTRAINT [FK_or_schedules_ob_consultation_id] FOREIGN KEY ([ob_consultation_id]) REFERENCES [dbo].[ob_consultations]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_or_schedules_surgeon_id' AND parent_object_id = OBJECT_ID(N'[dbo].[or_schedules]'))
    ALTER TABLE [dbo].[or_schedules] ADD CONSTRAINT [FK_or_schedules_surgeon_id] FOREIGN KEY ([surgeon_id]) REFERENCES [dbo].[health_care_professionals]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_or_schedules_anesthesiologist_id' AND parent_object_id = OBJECT_ID(N'[dbo].[or_schedules]'))
    ALTER TABLE [dbo].[or_schedules] ADD CONSTRAINT [FK_or_schedules_anesthesiologist_id] FOREIGN KEY ([anesthesiologist_id]) REFERENCES [dbo].[health_care_professionals]([id]) ON DELETE NO ACTION;
-- dr_rooms
IF OBJECT_ID(N'[dbo].[dr_rooms]', N'U') IS NOT NULL
    DROP TABLE [dbo].[dr_rooms];
GO

CREATE TABLE [dbo].[dr_rooms] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [location] NVARCHAR(100) NULL,
    [room_type] NVARCHAR(50) NULL,
    [capacity] INT NOT NULL DEFAULT (1),
    [equipment] NVARCHAR(MAX) NULL,
    [special_requirements] NVARCHAR(MAX) NULL,
    [status] NVARCHAR(30) NOT NULL DEFAULT (N'Available'),
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_dr_rooms] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_dr_rooms_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[dr_rooms]'))
    ALTER TABLE [dbo].[dr_rooms] ADD CONSTRAINT [UQ_dr_rooms_code__tenant_id__history] UNIQUE (code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_dr_rooms_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[dr_rooms]'))
    CREATE NONCLUSTERED INDEX [IX_dr_rooms_tenant_id] ON [dbo].[dr_rooms]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_dr_rooms_history' AND object_id = OBJECT_ID(N'[dbo].[dr_rooms]'))
    CREATE NONCLUSTERED INDEX [IX_dr_rooms_history] ON [dbo].[dr_rooms]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_dr_rooms_is_active' AND object_id = OBJECT_ID(N'[dbo].[dr_rooms]'))
    CREATE NONCLUSTERED INDEX [IX_dr_rooms_is_active] ON [dbo].[dr_rooms]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_dr_rooms_created_date' AND object_id = OBJECT_ID(N'[dbo].[dr_rooms]'))
    CREATE NONCLUSTERED INDEX [IX_dr_rooms_created_date] ON [dbo].[dr_rooms]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_dr_rooms_status' AND object_id = OBJECT_ID(N'[dbo].[dr_rooms]'))
    CREATE NONCLUSTERED INDEX [IX_dr_rooms_status] ON [dbo].[dr_rooms]([status] ASC);
-- dr_schedules
IF OBJECT_ID(N'[dbo].[dr_schedules]', N'U') IS NOT NULL
    DROP TABLE [dbo].[dr_schedules];
GO

CREATE TABLE [dbo].[dr_schedules] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [schedule_code] NVARCHAR(30) NOT NULL,
    [dr_room_id] BIGINT NOT NULL,
    [patient_id] BIGINT NOT NULL,
    [er_admission_id] BIGINT NULL,
    [admission_id] BIGINT NULL,
    [ob_consultation_id] BIGINT NULL,
    [attending_physician_id] BIGINT NULL,
    [pediatrician_id] BIGINT NULL,
    [anesthesiologist_id] BIGINT NULL,
    [midwife_id] BIGINT NULL,
    [delivery_type] NVARCHAR(50) NULL,
    [delivery_description] NVARCHAR(MAX) NULL,
    [scheduled_date] DATE NOT NULL,
    [start_time] TIME NULL,
    [end_time] TIME NULL,
    [status] NVARCHAR(30) NOT NULL DEFAULT (N'Scheduled'),
    [gestational_age] NVARCHAR(20) NULL,
    [lmp_date] DATE NULL,
    [edc_date] DATE NULL,
    [gravida] TINYINT NULL,
    [para] TINYINT NULL,
    [full_term] TINYINT NULL,
    [preterm] TINYINT NULL,
    [abortion] TINYINT NULL,
    [living_children] TINYINT NULL,
    [pre_op_notes] NVARCHAR(MAX) NULL,
    [intra_op_notes] NVARCHAR(MAX) NULL,
    [post_op_notes] NVARCHAR(MAX) NULL,
    [baby_sex] NVARCHAR(10) NULL,
    [baby_weight] DECIMAL(6,2) NULL,
    [baby_length] DECIMAL(5,2) NULL,
    [head_circumference] DECIMAL(5,2) NULL,
    [apgar_score_1min] TINYINT NULL,
    [apgar_score_5min] TINYINT NULL,
    [apgar_score_10min] TINYINT NULL,
    [delivery_complications] NVARCHAR(MAX) NULL,
    [newborn_outcome] NVARCHAR(30) NULL,
    [history_of_present_illness] NVARCHAR(MAX) NULL,
    [physical_exam_findings] NVARCHAR(MAX) NULL,
    [assessment] NVARCHAR(MAX) NULL,
    [orders_summary] NVARCHAR(MAX) NULL,
    [treatment_notes] NVARCHAR(MAX) NULL,
    [disposition] NVARCHAR(30) NOT NULL DEFAULT (N'Pending'),
    [discharge_instructions] NVARCHAR(MAX) NULL,
    [disposition_datetime] DATETIME2 NULL,
    [cancellation_reason] NVARCHAR(MAX) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_dr_schedules] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_dr_schedules_schedule_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[dr_schedules]'))
    ALTER TABLE [dbo].[dr_schedules] ADD CONSTRAINT [UQ_dr_schedules_schedule_code__tenant_id__history] UNIQUE (schedule_code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_dr_schedules_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[dr_schedules]'))
    CREATE NONCLUSTERED INDEX [IX_dr_schedules_tenant_id] ON [dbo].[dr_schedules]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_dr_schedules_dr_room_id' AND object_id = OBJECT_ID(N'[dbo].[dr_schedules]'))
    CREATE NONCLUSTERED INDEX [IX_dr_schedules_dr_room_id] ON [dbo].[dr_schedules]([dr_room_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_dr_schedules_patient_id' AND object_id = OBJECT_ID(N'[dbo].[dr_schedules]'))
    CREATE NONCLUSTERED INDEX [IX_dr_schedules_patient_id] ON [dbo].[dr_schedules]([patient_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_dr_schedules_er_admission_id' AND object_id = OBJECT_ID(N'[dbo].[dr_schedules]'))
    CREATE NONCLUSTERED INDEX [IX_dr_schedules_er_admission_id] ON [dbo].[dr_schedules]([er_admission_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_dr_schedules_admission_id' AND object_id = OBJECT_ID(N'[dbo].[dr_schedules]'))
    CREATE NONCLUSTERED INDEX [IX_dr_schedules_admission_id] ON [dbo].[dr_schedules]([admission_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_dr_schedules_ob_consultation_id' AND object_id = OBJECT_ID(N'[dbo].[dr_schedules]'))
    CREATE NONCLUSTERED INDEX [IX_dr_schedules_ob_consultation_id] ON [dbo].[dr_schedules]([ob_consultation_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_dr_schedules_attending_physician_id' AND object_id = OBJECT_ID(N'[dbo].[dr_schedules]'))
    CREATE NONCLUSTERED INDEX [IX_dr_schedules_attending_physician_id] ON [dbo].[dr_schedules]([attending_physician_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_dr_schedules_pediatrician_id' AND object_id = OBJECT_ID(N'[dbo].[dr_schedules]'))
    CREATE NONCLUSTERED INDEX [IX_dr_schedules_pediatrician_id] ON [dbo].[dr_schedules]([pediatrician_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_dr_schedules_anesthesiologist_id' AND object_id = OBJECT_ID(N'[dbo].[dr_schedules]'))
    CREATE NONCLUSTERED INDEX [IX_dr_schedules_anesthesiologist_id] ON [dbo].[dr_schedules]([anesthesiologist_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_dr_schedules_midwife_id' AND object_id = OBJECT_ID(N'[dbo].[dr_schedules]'))
    CREATE NONCLUSTERED INDEX [IX_dr_schedules_midwife_id] ON [dbo].[dr_schedules]([midwife_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_dr_schedules_history' AND object_id = OBJECT_ID(N'[dbo].[dr_schedules]'))
    CREATE NONCLUSTERED INDEX [IX_dr_schedules_history] ON [dbo].[dr_schedules]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_dr_schedules_is_active' AND object_id = OBJECT_ID(N'[dbo].[dr_schedules]'))
    CREATE NONCLUSTERED INDEX [IX_dr_schedules_is_active] ON [dbo].[dr_schedules]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_dr_schedules_created_date' AND object_id = OBJECT_ID(N'[dbo].[dr_schedules]'))
    CREATE NONCLUSTERED INDEX [IX_dr_schedules_created_date] ON [dbo].[dr_schedules]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_dr_schedules_status' AND object_id = OBJECT_ID(N'[dbo].[dr_schedules]'))
    CREATE NONCLUSTERED INDEX [IX_dr_schedules_status] ON [dbo].[dr_schedules]([status] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_dr_schedules_dr_room_id' AND parent_object_id = OBJECT_ID(N'[dbo].[dr_schedules]'))
    ALTER TABLE [dbo].[dr_schedules] ADD CONSTRAINT [FK_dr_schedules_dr_room_id] FOREIGN KEY ([dr_room_id]) REFERENCES [dbo].[dr_rooms]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_dr_schedules_patient_id' AND parent_object_id = OBJECT_ID(N'[dbo].[dr_schedules]'))
    ALTER TABLE [dbo].[dr_schedules] ADD CONSTRAINT [FK_dr_schedules_patient_id] FOREIGN KEY ([patient_id]) REFERENCES [dbo].[patients]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_dr_schedules_er_admission_id' AND parent_object_id = OBJECT_ID(N'[dbo].[dr_schedules]'))
    ALTER TABLE [dbo].[dr_schedules] ADD CONSTRAINT [FK_dr_schedules_er_admission_id] FOREIGN KEY ([er_admission_id]) REFERENCES [dbo].[er_admissions]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_dr_schedules_admission_id' AND parent_object_id = OBJECT_ID(N'[dbo].[dr_schedules]'))
    ALTER TABLE [dbo].[dr_schedules] ADD CONSTRAINT [FK_dr_schedules_admission_id] FOREIGN KEY ([admission_id]) REFERENCES [dbo].[admissions]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_dr_schedules_ob_consultation_id' AND parent_object_id = OBJECT_ID(N'[dbo].[dr_schedules]'))
    ALTER TABLE [dbo].[dr_schedules] ADD CONSTRAINT [FK_dr_schedules_ob_consultation_id] FOREIGN KEY ([ob_consultation_id]) REFERENCES [dbo].[ob_consultations]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_dr_schedules_attending_physician_id' AND parent_object_id = OBJECT_ID(N'[dbo].[dr_schedules]'))
    ALTER TABLE [dbo].[dr_schedules] ADD CONSTRAINT [FK_dr_schedules_attending_physician_id] FOREIGN KEY ([attending_physician_id]) REFERENCES [dbo].[health_care_professionals]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_dr_schedules_pediatrician_id' AND parent_object_id = OBJECT_ID(N'[dbo].[dr_schedules]'))
    ALTER TABLE [dbo].[dr_schedules] ADD CONSTRAINT [FK_dr_schedules_pediatrician_id] FOREIGN KEY ([pediatrician_id]) REFERENCES [dbo].[health_care_professionals]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_dr_schedules_anesthesiologist_id' AND parent_object_id = OBJECT_ID(N'[dbo].[dr_schedules]'))
    ALTER TABLE [dbo].[dr_schedules] ADD CONSTRAINT [FK_dr_schedules_anesthesiologist_id] FOREIGN KEY ([anesthesiologist_id]) REFERENCES [dbo].[health_care_professionals]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_dr_schedules_midwife_id' AND parent_object_id = OBJECT_ID(N'[dbo].[dr_schedules]'))
    ALTER TABLE [dbo].[dr_schedules] ADD CONSTRAINT [FK_dr_schedules_midwife_id] FOREIGN KEY ([midwife_id]) REFERENCES [dbo].[health_care_professionals]([id]) ON DELETE NO ACTION;
-- drugs
IF OBJECT_ID(N'[dbo].[drugs]', N'U') IS NOT NULL
    DROP TABLE [dbo].[drugs];
GO

CREATE TABLE [dbo].[drugs] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [drug_code] NVARCHAR(30) NOT NULL,
    [philhealth_item_code] NVARCHAR(50) NULL,
    [generic_name] NVARCHAR(200) NOT NULL,
    [brand_name] NVARCHAR(200) NULL,
    [drug_strength] NVARCHAR(50) NULL,
    [dosage_form] NVARCHAR(50) NULL,
    [therapeutic_class] NVARCHAR(100) NULL,
    [drug_schedule] NVARCHAR(30) NULL,
    [route] NVARCHAR(30) NULL,
    [active_ingredient] NVARCHAR(200) NULL,
    [manufacturer] NVARCHAR(200) NULL,
    [side_effects] NVARCHAR(MAX) NULL,
    [contraindications] NVARCHAR(MAX) NULL,
    [reorder_level] INT NOT NULL DEFAULT (0),
    [reorder_qty] INT NOT NULL DEFAULT (0),
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_drugs] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_drugs_drug_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[drugs]'))
    ALTER TABLE [dbo].[drugs] ADD CONSTRAINT [UQ_drugs_drug_code__tenant_id__history] UNIQUE (drug_code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_drugs_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[drugs]'))
    CREATE NONCLUSTERED INDEX [IX_drugs_tenant_id] ON [dbo].[drugs]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_drugs_history' AND object_id = OBJECT_ID(N'[dbo].[drugs]'))
    CREATE NONCLUSTERED INDEX [IX_drugs_history] ON [dbo].[drugs]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_drugs_is_active' AND object_id = OBJECT_ID(N'[dbo].[drugs]'))
    CREATE NONCLUSTERED INDEX [IX_drugs_is_active] ON [dbo].[drugs]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_drugs_created_date' AND object_id = OBJECT_ID(N'[dbo].[drugs]'))
    CREATE NONCLUSTERED INDEX [IX_drugs_created_date] ON [dbo].[drugs]([created_date] ASC);
-- drug_batches
IF OBJECT_ID(N'[dbo].[drug_batches]', N'U') IS NOT NULL
    DROP TABLE [dbo].[drug_batches];
GO

CREATE TABLE [dbo].[drug_batches] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [drug_id] BIGINT NOT NULL,
    [batch_no] NVARCHAR(50) NOT NULL,
    [expiry_date] DATE NOT NULL,
    [quantity_on_hand] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [unit_cost] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [selling_price] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [location] NVARCHAR(100) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_drug_batches] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_drug_batches_drug_id__batch_no__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[drug_batches]'))
    ALTER TABLE [dbo].[drug_batches] ADD CONSTRAINT [UQ_drug_batches_drug_id__batch_no__tenant_id__history] UNIQUE (drug_id, batch_no, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_drug_batches_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[drug_batches]'))
    CREATE NONCLUSTERED INDEX [IX_drug_batches_tenant_id] ON [dbo].[drug_batches]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_drug_batches_drug_id' AND object_id = OBJECT_ID(N'[dbo].[drug_batches]'))
    CREATE NONCLUSTERED INDEX [IX_drug_batches_drug_id] ON [dbo].[drug_batches]([drug_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_drug_batches_history' AND object_id = OBJECT_ID(N'[dbo].[drug_batches]'))
    CREATE NONCLUSTERED INDEX [IX_drug_batches_history] ON [dbo].[drug_batches]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_drug_batches_is_active' AND object_id = OBJECT_ID(N'[dbo].[drug_batches]'))
    CREATE NONCLUSTERED INDEX [IX_drug_batches_is_active] ON [dbo].[drug_batches]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_drug_batches_created_date' AND object_id = OBJECT_ID(N'[dbo].[drug_batches]'))
    CREATE NONCLUSTERED INDEX [IX_drug_batches_created_date] ON [dbo].[drug_batches]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_drug_batches_drug_id' AND parent_object_id = OBJECT_ID(N'[dbo].[drug_batches]'))
    ALTER TABLE [dbo].[drug_batches] ADD CONSTRAINT [FK_drug_batches_drug_id] FOREIGN KEY ([drug_id]) REFERENCES [dbo].[drugs]([id]) ON DELETE NO ACTION;
-- purchase_orders
IF OBJECT_ID(N'[dbo].[purchase_orders]', N'U') IS NOT NULL
    DROP TABLE [dbo].[purchase_orders];
GO

CREATE TABLE [dbo].[purchase_orders] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [po_code] NVARCHAR(30) NOT NULL,
    [supplier_id] BIGINT NULL,
    [order_date] DATE NOT NULL,
    [expected_date] DATE NULL,
    [status] NVARCHAR(30) NOT NULL DEFAULT (N'Draft'),
    [total_amount] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [notes] NVARCHAR(MAX) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_purchase_orders] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_purchase_orders_po_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[purchase_orders]'))
    ALTER TABLE [dbo].[purchase_orders] ADD CONSTRAINT [UQ_purchase_orders_po_code__tenant_id__history] UNIQUE (po_code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_purchase_orders_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[purchase_orders]'))
    CREATE NONCLUSTERED INDEX [IX_purchase_orders_tenant_id] ON [dbo].[purchase_orders]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_purchase_orders_supplier_id' AND object_id = OBJECT_ID(N'[dbo].[purchase_orders]'))
    CREATE NONCLUSTERED INDEX [IX_purchase_orders_supplier_id] ON [dbo].[purchase_orders]([supplier_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_purchase_orders_history' AND object_id = OBJECT_ID(N'[dbo].[purchase_orders]'))
    CREATE NONCLUSTERED INDEX [IX_purchase_orders_history] ON [dbo].[purchase_orders]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_purchase_orders_is_active' AND object_id = OBJECT_ID(N'[dbo].[purchase_orders]'))
    CREATE NONCLUSTERED INDEX [IX_purchase_orders_is_active] ON [dbo].[purchase_orders]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_purchase_orders_created_date' AND object_id = OBJECT_ID(N'[dbo].[purchase_orders]'))
    CREATE NONCLUSTERED INDEX [IX_purchase_orders_created_date] ON [dbo].[purchase_orders]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_purchase_orders_status' AND object_id = OBJECT_ID(N'[dbo].[purchase_orders]'))
    CREATE NONCLUSTERED INDEX [IX_purchase_orders_status] ON [dbo].[purchase_orders]([status] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_purchase_orders_supplier_id' AND parent_object_id = OBJECT_ID(N'[dbo].[purchase_orders]'))
    ALTER TABLE [dbo].[purchase_orders] ADD CONSTRAINT [FK_purchase_orders_supplier_id] FOREIGN KEY ([supplier_id]) REFERENCES [dbo].[suppliers]([id]) ON DELETE SET NULL;
-- purchase_order_items
IF OBJECT_ID(N'[dbo].[purchase_order_items]', N'U') IS NOT NULL
    DROP TABLE [dbo].[purchase_order_items];
GO

CREATE TABLE [dbo].[purchase_order_items] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [purchase_order_id] BIGINT NOT NULL,
    [drug_id] BIGINT NOT NULL,
    [quantity_ordered] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [quantity_received] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [unit_cost] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [total_cost] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [batch_no] NVARCHAR(50) NULL,
    [expiry_date] DATE NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_purchase_order_items] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_purchase_order_items_purchase_order_id' AND object_id = OBJECT_ID(N'[dbo].[purchase_order_items]'))
    CREATE NONCLUSTERED INDEX [IX_purchase_order_items_purchase_order_id] ON [dbo].[purchase_order_items]([purchase_order_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_purchase_order_items_drug_id' AND object_id = OBJECT_ID(N'[dbo].[purchase_order_items]'))
    CREATE NONCLUSTERED INDEX [IX_purchase_order_items_drug_id] ON [dbo].[purchase_order_items]([drug_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_purchase_order_items_history' AND object_id = OBJECT_ID(N'[dbo].[purchase_order_items]'))
    CREATE NONCLUSTERED INDEX [IX_purchase_order_items_history] ON [dbo].[purchase_order_items]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_purchase_order_items_is_active' AND object_id = OBJECT_ID(N'[dbo].[purchase_order_items]'))
    CREATE NONCLUSTERED INDEX [IX_purchase_order_items_is_active] ON [dbo].[purchase_order_items]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_purchase_order_items_created_date' AND object_id = OBJECT_ID(N'[dbo].[purchase_order_items]'))
    CREATE NONCLUSTERED INDEX [IX_purchase_order_items_created_date] ON [dbo].[purchase_order_items]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_purchase_order_items_purchase_order_id' AND parent_object_id = OBJECT_ID(N'[dbo].[purchase_order_items]'))
    ALTER TABLE [dbo].[purchase_order_items] ADD CONSTRAINT [FK_purchase_order_items_purchase_order_id] FOREIGN KEY ([purchase_order_id]) REFERENCES [dbo].[purchase_orders]([id]) ON DELETE CASCADE;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_purchase_order_items_drug_id' AND parent_object_id = OBJECT_ID(N'[dbo].[purchase_order_items]'))
    ALTER TABLE [dbo].[purchase_order_items] ADD CONSTRAINT [FK_purchase_order_items_drug_id] FOREIGN KEY ([drug_id]) REFERENCES [dbo].[drugs]([id]) ON DELETE NO ACTION;
-- stock_adjustments
IF OBJECT_ID(N'[dbo].[stock_adjustments]', N'U') IS NOT NULL
    DROP TABLE [dbo].[stock_adjustments];
GO

CREATE TABLE [dbo].[stock_adjustments] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [adjustment_code] NVARCHAR(30) NOT NULL,
    [drug_id] BIGINT NULL,
    [drug_batch_id] BIGINT NULL,
    [adjustment_type] NVARCHAR(30) NOT NULL,
    [quantity] DECIMAL(12,2) NOT NULL,
    [unit_cost] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [reason] NVARCHAR(MAX) NULL,
    [reference_type] NVARCHAR(50) NULL,
    [reference_id] BIGINT NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_stock_adjustments] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_stock_adjustments_adjustment_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[stock_adjustments]'))
    ALTER TABLE [dbo].[stock_adjustments] ADD CONSTRAINT [UQ_stock_adjustments_adjustment_code__tenant_id__history] UNIQUE (adjustment_code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_stock_adjustments_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[stock_adjustments]'))
    CREATE NONCLUSTERED INDEX [IX_stock_adjustments_tenant_id] ON [dbo].[stock_adjustments]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_stock_adjustments_drug_id' AND object_id = OBJECT_ID(N'[dbo].[stock_adjustments]'))
    CREATE NONCLUSTERED INDEX [IX_stock_adjustments_drug_id] ON [dbo].[stock_adjustments]([drug_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_stock_adjustments_drug_batch_id' AND object_id = OBJECT_ID(N'[dbo].[stock_adjustments]'))
    CREATE NONCLUSTERED INDEX [IX_stock_adjustments_drug_batch_id] ON [dbo].[stock_adjustments]([drug_batch_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_stock_adjustments_history' AND object_id = OBJECT_ID(N'[dbo].[stock_adjustments]'))
    CREATE NONCLUSTERED INDEX [IX_stock_adjustments_history] ON [dbo].[stock_adjustments]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_stock_adjustments_is_active' AND object_id = OBJECT_ID(N'[dbo].[stock_adjustments]'))
    CREATE NONCLUSTERED INDEX [IX_stock_adjustments_is_active] ON [dbo].[stock_adjustments]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_stock_adjustments_created_date' AND object_id = OBJECT_ID(N'[dbo].[stock_adjustments]'))
    CREATE NONCLUSTERED INDEX [IX_stock_adjustments_created_date] ON [dbo].[stock_adjustments]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_stock_adjustments_drug_id' AND parent_object_id = OBJECT_ID(N'[dbo].[stock_adjustments]'))
    ALTER TABLE [dbo].[stock_adjustments] ADD CONSTRAINT [FK_stock_adjustments_drug_id] FOREIGN KEY ([drug_id]) REFERENCES [dbo].[drugs]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_stock_adjustments_drug_batch_id' AND parent_object_id = OBJECT_ID(N'[dbo].[stock_adjustments]'))
    ALTER TABLE [dbo].[stock_adjustments] ADD CONSTRAINT [FK_stock_adjustments_drug_batch_id] FOREIGN KEY ([drug_batch_id]) REFERENCES [dbo].[drug_batches]([id]) ON DELETE NO ACTION;
-- lab_requests
IF OBJECT_ID(N'[dbo].[lab_requests]', N'U') IS NOT NULL
    DROP TABLE [dbo].[lab_requests];
GO

CREATE TABLE [dbo].[lab_requests] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [er_admission_id] BIGINT NULL,
    [opd_consultation_id] BIGINT NULL,
    [admission_id] BIGINT NULL,
    [dialysis_session_id] BIGINT NULL,
    [ob_consultation_id] BIGINT NULL,
    [or_schedule_id] BIGINT NULL,
    [dr_schedule_id] BIGINT NULL,
    [request_code] NVARCHAR(30) NOT NULL,
    [patient_id] BIGINT NOT NULL,
    [requesting_department_id] BIGINT NULL,
    [requesting_doctor_id] BIGINT NULL,
    [priority] NVARCHAR(20) NOT NULL DEFAULT (N'Routine'),
    [request_notes] NVARCHAR(MAX) NULL,
    [status] NVARCHAR(30) NOT NULL DEFAULT (N'Pending'),
    [billing_status] NVARCHAR(30) NOT NULL DEFAULT (N'Pending'),
    [billing_id] BIGINT NULL,
    [total_charge] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_lab_requests] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_lab_requests_request_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[lab_requests]'))
    ALTER TABLE [dbo].[lab_requests] ADD CONSTRAINT [UQ_lab_requests_request_code__tenant_id__history] UNIQUE (request_code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_requests_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[lab_requests]'))
    CREATE NONCLUSTERED INDEX [IX_lab_requests_tenant_id] ON [dbo].[lab_requests]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_requests_er_admission_id' AND object_id = OBJECT_ID(N'[dbo].[lab_requests]'))
    CREATE NONCLUSTERED INDEX [IX_lab_requests_er_admission_id] ON [dbo].[lab_requests]([er_admission_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_requests_opd_consultation_id' AND object_id = OBJECT_ID(N'[dbo].[lab_requests]'))
    CREATE NONCLUSTERED INDEX [IX_lab_requests_opd_consultation_id] ON [dbo].[lab_requests]([opd_consultation_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_requests_admission_id' AND object_id = OBJECT_ID(N'[dbo].[lab_requests]'))
    CREATE NONCLUSTERED INDEX [IX_lab_requests_admission_id] ON [dbo].[lab_requests]([admission_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_requests_dialysis_session_id' AND object_id = OBJECT_ID(N'[dbo].[lab_requests]'))
    CREATE NONCLUSTERED INDEX [IX_lab_requests_dialysis_session_id] ON [dbo].[lab_requests]([dialysis_session_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_requests_ob_consultation_id' AND object_id = OBJECT_ID(N'[dbo].[lab_requests]'))
    CREATE NONCLUSTERED INDEX [IX_lab_requests_ob_consultation_id] ON [dbo].[lab_requests]([ob_consultation_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_requests_or_schedule_id' AND object_id = OBJECT_ID(N'[dbo].[lab_requests]'))
    CREATE NONCLUSTERED INDEX [IX_lab_requests_or_schedule_id] ON [dbo].[lab_requests]([or_schedule_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_requests_dr_schedule_id' AND object_id = OBJECT_ID(N'[dbo].[lab_requests]'))
    CREATE NONCLUSTERED INDEX [IX_lab_requests_dr_schedule_id] ON [dbo].[lab_requests]([dr_schedule_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_requests_patient_id' AND object_id = OBJECT_ID(N'[dbo].[lab_requests]'))
    CREATE NONCLUSTERED INDEX [IX_lab_requests_patient_id] ON [dbo].[lab_requests]([patient_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_requests_requesting_department_id' AND object_id = OBJECT_ID(N'[dbo].[lab_requests]'))
    CREATE NONCLUSTERED INDEX [IX_lab_requests_requesting_department_id] ON [dbo].[lab_requests]([requesting_department_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_requests_requesting_doctor_id' AND object_id = OBJECT_ID(N'[dbo].[lab_requests]'))
    CREATE NONCLUSTERED INDEX [IX_lab_requests_requesting_doctor_id] ON [dbo].[lab_requests]([requesting_doctor_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_requests_history' AND object_id = OBJECT_ID(N'[dbo].[lab_requests]'))
    CREATE NONCLUSTERED INDEX [IX_lab_requests_history] ON [dbo].[lab_requests]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_requests_is_active' AND object_id = OBJECT_ID(N'[dbo].[lab_requests]'))
    CREATE NONCLUSTERED INDEX [IX_lab_requests_is_active] ON [dbo].[lab_requests]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_requests_created_date' AND object_id = OBJECT_ID(N'[dbo].[lab_requests]'))
    CREATE NONCLUSTERED INDEX [IX_lab_requests_created_date] ON [dbo].[lab_requests]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_requests_status' AND object_id = OBJECT_ID(N'[dbo].[lab_requests]'))
    CREATE NONCLUSTERED INDEX [IX_lab_requests_status] ON [dbo].[lab_requests]([status] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_lab_requests_er_admission_id' AND parent_object_id = OBJECT_ID(N'[dbo].[lab_requests]'))
    ALTER TABLE [dbo].[lab_requests] ADD CONSTRAINT [FK_lab_requests_er_admission_id] FOREIGN KEY ([er_admission_id]) REFERENCES [dbo].[er_admissions]([id]) ON DELETE SET NULL;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_lab_requests_opd_consultation_id' AND parent_object_id = OBJECT_ID(N'[dbo].[lab_requests]'))
    ALTER TABLE [dbo].[lab_requests] ADD CONSTRAINT [FK_lab_requests_opd_consultation_id] FOREIGN KEY ([opd_consultation_id]) REFERENCES [dbo].[opd_consultations]([id]) ON DELETE SET NULL;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_lab_requests_admission_id' AND parent_object_id = OBJECT_ID(N'[dbo].[lab_requests]'))
    ALTER TABLE [dbo].[lab_requests] ADD CONSTRAINT [FK_lab_requests_admission_id] FOREIGN KEY ([admission_id]) REFERENCES [dbo].[admissions]([id]) ON DELETE SET NULL;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_lab_requests_dialysis_session_id' AND parent_object_id = OBJECT_ID(N'[dbo].[lab_requests]'))
    ALTER TABLE [dbo].[lab_requests] ADD CONSTRAINT [FK_lab_requests_dialysis_session_id] FOREIGN KEY ([dialysis_session_id]) REFERENCES [dbo].[dialysis_sessions]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_lab_requests_ob_consultation_id' AND parent_object_id = OBJECT_ID(N'[dbo].[lab_requests]'))
    ALTER TABLE [dbo].[lab_requests] ADD CONSTRAINT [FK_lab_requests_ob_consultation_id] FOREIGN KEY ([ob_consultation_id]) REFERENCES [dbo].[ob_consultations]([id]) ON DELETE SET NULL;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_lab_requests_or_schedule_id' AND parent_object_id = OBJECT_ID(N'[dbo].[lab_requests]'))
    ALTER TABLE [dbo].[lab_requests] ADD CONSTRAINT [FK_lab_requests_or_schedule_id] FOREIGN KEY ([or_schedule_id]) REFERENCES [dbo].[or_schedules]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_lab_requests_dr_schedule_id' AND parent_object_id = OBJECT_ID(N'[dbo].[lab_requests]'))
    ALTER TABLE [dbo].[lab_requests] ADD CONSTRAINT [FK_lab_requests_dr_schedule_id] FOREIGN KEY ([dr_schedule_id]) REFERENCES [dbo].[dr_schedules]([id]) ON DELETE SET NULL;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_lab_requests_patient_id' AND parent_object_id = OBJECT_ID(N'[dbo].[lab_requests]'))
    ALTER TABLE [dbo].[lab_requests] ADD CONSTRAINT [FK_lab_requests_patient_id] FOREIGN KEY ([patient_id]) REFERENCES [dbo].[patients]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_lab_requests_requesting_department_id' AND parent_object_id = OBJECT_ID(N'[dbo].[lab_requests]'))
    ALTER TABLE [dbo].[lab_requests] ADD CONSTRAINT [FK_lab_requests_requesting_department_id] FOREIGN KEY ([requesting_department_id]) REFERENCES [dbo].[departments]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_lab_requests_requesting_doctor_id' AND parent_object_id = OBJECT_ID(N'[dbo].[lab_requests]'))
    ALTER TABLE [dbo].[lab_requests] ADD CONSTRAINT [FK_lab_requests_requesting_doctor_id] FOREIGN KEY ([requesting_doctor_id]) REFERENCES [dbo].[health_care_professionals]([id]) ON DELETE NO ACTION;
-- lab_request_items
IF OBJECT_ID(N'[dbo].[lab_request_items]', N'U') IS NOT NULL
    DROP TABLE [dbo].[lab_request_items];
GO

CREATE TABLE [dbo].[lab_request_items] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [lab_request_id] BIGINT NOT NULL,
    [item_type] NVARCHAR(20) NOT NULL,
    [lab_test_id] BIGINT NULL,
    [lab_panel_id] BIGINT NULL,
    [test_name] NVARCHAR(200) NOT NULL,
    [charge_amount] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [status] NVARCHAR(30) NOT NULL DEFAULT (N'Pending'),
    [result_value] NVARCHAR(MAX) NULL,
    [result_text] NVARCHAR(MAX) NULL,
    [result_categorical] NVARCHAR(100) NULL,
    [flag] NVARCHAR(10) NULL,
    [reference_range] NVARCHAR(100) NULL,
    [validated_by] BIGINT NULL,
    [validated_at] DATETIME2 NULL,
    [result_notes] NVARCHAR(MAX) NULL,
    [result_date] DATETIME2 NULL,
    [performed_by] BIGINT NULL,
    [verified_by] BIGINT NULL,
    [verified_date] DATETIME2 NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_lab_request_items] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_request_items_lab_request_id' AND object_id = OBJECT_ID(N'[dbo].[lab_request_items]'))
    CREATE NONCLUSTERED INDEX [IX_lab_request_items_lab_request_id] ON [dbo].[lab_request_items]([lab_request_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_request_items_lab_test_id' AND object_id = OBJECT_ID(N'[dbo].[lab_request_items]'))
    CREATE NONCLUSTERED INDEX [IX_lab_request_items_lab_test_id] ON [dbo].[lab_request_items]([lab_test_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_request_items_lab_panel_id' AND object_id = OBJECT_ID(N'[dbo].[lab_request_items]'))
    CREATE NONCLUSTERED INDEX [IX_lab_request_items_lab_panel_id] ON [dbo].[lab_request_items]([lab_panel_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_request_items_performed_by' AND object_id = OBJECT_ID(N'[dbo].[lab_request_items]'))
    CREATE NONCLUSTERED INDEX [IX_lab_request_items_performed_by] ON [dbo].[lab_request_items]([performed_by] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_request_items_verified_by' AND object_id = OBJECT_ID(N'[dbo].[lab_request_items]'))
    CREATE NONCLUSTERED INDEX [IX_lab_request_items_verified_by] ON [dbo].[lab_request_items]([verified_by] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_request_items_history' AND object_id = OBJECT_ID(N'[dbo].[lab_request_items]'))
    CREATE NONCLUSTERED INDEX [IX_lab_request_items_history] ON [dbo].[lab_request_items]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_request_items_is_active' AND object_id = OBJECT_ID(N'[dbo].[lab_request_items]'))
    CREATE NONCLUSTERED INDEX [IX_lab_request_items_is_active] ON [dbo].[lab_request_items]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_request_items_created_date' AND object_id = OBJECT_ID(N'[dbo].[lab_request_items]'))
    CREATE NONCLUSTERED INDEX [IX_lab_request_items_created_date] ON [dbo].[lab_request_items]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_lab_request_items_status' AND object_id = OBJECT_ID(N'[dbo].[lab_request_items]'))
    CREATE NONCLUSTERED INDEX [IX_lab_request_items_status] ON [dbo].[lab_request_items]([status] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_lab_request_items_lab_request_id' AND parent_object_id = OBJECT_ID(N'[dbo].[lab_request_items]'))
    ALTER TABLE [dbo].[lab_request_items] ADD CONSTRAINT [FK_lab_request_items_lab_request_id] FOREIGN KEY ([lab_request_id]) REFERENCES [dbo].[lab_requests]([id]) ON DELETE CASCADE;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_lab_request_items_lab_test_id' AND parent_object_id = OBJECT_ID(N'[dbo].[lab_request_items]'))
    ALTER TABLE [dbo].[lab_request_items] ADD CONSTRAINT [FK_lab_request_items_lab_test_id] FOREIGN KEY ([lab_test_id]) REFERENCES [dbo].[lab_tests]([id]) ON DELETE SET NULL;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_lab_request_items_lab_panel_id' AND parent_object_id = OBJECT_ID(N'[dbo].[lab_request_items]'))
    ALTER TABLE [dbo].[lab_request_items] ADD CONSTRAINT [FK_lab_request_items_lab_panel_id] FOREIGN KEY ([lab_panel_id]) REFERENCES [dbo].[lab_panels]([id]) ON DELETE SET NULL;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_lab_request_items_performed_by' AND parent_object_id = OBJECT_ID(N'[dbo].[lab_request_items]'))
    ALTER TABLE [dbo].[lab_request_items] ADD CONSTRAINT [FK_lab_request_items_performed_by] FOREIGN KEY ([performed_by]) REFERENCES [dbo].[users]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_lab_request_items_verified_by' AND parent_object_id = OBJECT_ID(N'[dbo].[lab_request_items]'))
    ALTER TABLE [dbo].[lab_request_items] ADD CONSTRAINT [FK_lab_request_items_verified_by] FOREIGN KEY ([verified_by]) REFERENCES [dbo].[users]([id]) ON DELETE NO ACTION;
-- csr_requests
IF OBJECT_ID(N'[dbo].[csr_requests]', N'U') IS NOT NULL
    DROP TABLE [dbo].[csr_requests];
GO

CREATE TABLE [dbo].[csr_requests] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [er_admission_id] BIGINT NULL,
    [opd_consultation_id] BIGINT NULL,
    [admission_id] BIGINT NULL,
    [dialysis_session_id] BIGINT NULL,
    [ob_consultation_id] BIGINT NULL,
    [or_schedule_id] BIGINT NULL,
    [dr_schedule_id] BIGINT NULL,
    [request_code] NVARCHAR(30) NOT NULL,
    [requesting_department_id] BIGINT NULL,
    [requesting_person] NVARCHAR(150) NULL,
    [requesting_doctor_id] BIGINT NULL,
    [status] NVARCHAR(30) NOT NULL DEFAULT (N'Pending'),
    [billing_status] NVARCHAR(30) NOT NULL DEFAULT (N'Pending'),
    [billing_id] BIGINT NULL,
    [total_amount] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [notes] NVARCHAR(MAX) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_csr_requests] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_csr_requests_request_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[csr_requests]'))
    ALTER TABLE [dbo].[csr_requests] ADD CONSTRAINT [UQ_csr_requests_request_code__tenant_id__history] UNIQUE (request_code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_csr_requests_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[csr_requests]'))
    CREATE NONCLUSTERED INDEX [IX_csr_requests_tenant_id] ON [dbo].[csr_requests]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_csr_requests_er_admission_id' AND object_id = OBJECT_ID(N'[dbo].[csr_requests]'))
    CREATE NONCLUSTERED INDEX [IX_csr_requests_er_admission_id] ON [dbo].[csr_requests]([er_admission_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_csr_requests_opd_consultation_id' AND object_id = OBJECT_ID(N'[dbo].[csr_requests]'))
    CREATE NONCLUSTERED INDEX [IX_csr_requests_opd_consultation_id] ON [dbo].[csr_requests]([opd_consultation_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_csr_requests_admission_id' AND object_id = OBJECT_ID(N'[dbo].[csr_requests]'))
    CREATE NONCLUSTERED INDEX [IX_csr_requests_admission_id] ON [dbo].[csr_requests]([admission_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_csr_requests_dialysis_session_id' AND object_id = OBJECT_ID(N'[dbo].[csr_requests]'))
    CREATE NONCLUSTERED INDEX [IX_csr_requests_dialysis_session_id] ON [dbo].[csr_requests]([dialysis_session_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_csr_requests_ob_consultation_id' AND object_id = OBJECT_ID(N'[dbo].[csr_requests]'))
    CREATE NONCLUSTERED INDEX [IX_csr_requests_ob_consultation_id] ON [dbo].[csr_requests]([ob_consultation_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_csr_requests_or_schedule_id' AND object_id = OBJECT_ID(N'[dbo].[csr_requests]'))
    CREATE NONCLUSTERED INDEX [IX_csr_requests_or_schedule_id] ON [dbo].[csr_requests]([or_schedule_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_csr_requests_dr_schedule_id' AND object_id = OBJECT_ID(N'[dbo].[csr_requests]'))
    CREATE NONCLUSTERED INDEX [IX_csr_requests_dr_schedule_id] ON [dbo].[csr_requests]([dr_schedule_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_csr_requests_requesting_department_id' AND object_id = OBJECT_ID(N'[dbo].[csr_requests]'))
    CREATE NONCLUSTERED INDEX [IX_csr_requests_requesting_department_id] ON [dbo].[csr_requests]([requesting_department_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_csr_requests_requesting_doctor_id' AND object_id = OBJECT_ID(N'[dbo].[csr_requests]'))
    CREATE NONCLUSTERED INDEX [IX_csr_requests_requesting_doctor_id] ON [dbo].[csr_requests]([requesting_doctor_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_csr_requests_history' AND object_id = OBJECT_ID(N'[dbo].[csr_requests]'))
    CREATE NONCLUSTERED INDEX [IX_csr_requests_history] ON [dbo].[csr_requests]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_csr_requests_is_active' AND object_id = OBJECT_ID(N'[dbo].[csr_requests]'))
    CREATE NONCLUSTERED INDEX [IX_csr_requests_is_active] ON [dbo].[csr_requests]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_csr_requests_created_date' AND object_id = OBJECT_ID(N'[dbo].[csr_requests]'))
    CREATE NONCLUSTERED INDEX [IX_csr_requests_created_date] ON [dbo].[csr_requests]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_csr_requests_status' AND object_id = OBJECT_ID(N'[dbo].[csr_requests]'))
    CREATE NONCLUSTERED INDEX [IX_csr_requests_status] ON [dbo].[csr_requests]([status] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_csr_requests_er_admission_id' AND parent_object_id = OBJECT_ID(N'[dbo].[csr_requests]'))
    ALTER TABLE [dbo].[csr_requests] ADD CONSTRAINT [FK_csr_requests_er_admission_id] FOREIGN KEY ([er_admission_id]) REFERENCES [dbo].[er_admissions]([id]) ON DELETE SET NULL;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_csr_requests_opd_consultation_id' AND parent_object_id = OBJECT_ID(N'[dbo].[csr_requests]'))
    ALTER TABLE [dbo].[csr_requests] ADD CONSTRAINT [FK_csr_requests_opd_consultation_id] FOREIGN KEY ([opd_consultation_id]) REFERENCES [dbo].[opd_consultations]([id]) ON DELETE SET NULL;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_csr_requests_admission_id' AND parent_object_id = OBJECT_ID(N'[dbo].[csr_requests]'))
    ALTER TABLE [dbo].[csr_requests] ADD CONSTRAINT [FK_csr_requests_admission_id] FOREIGN KEY ([admission_id]) REFERENCES [dbo].[admissions]([id]) ON DELETE SET NULL;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_csr_requests_dialysis_session_id' AND parent_object_id = OBJECT_ID(N'[dbo].[csr_requests]'))
    ALTER TABLE [dbo].[csr_requests] ADD CONSTRAINT [FK_csr_requests_dialysis_session_id] FOREIGN KEY ([dialysis_session_id]) REFERENCES [dbo].[dialysis_sessions]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_csr_requests_ob_consultation_id' AND parent_object_id = OBJECT_ID(N'[dbo].[csr_requests]'))
    ALTER TABLE [dbo].[csr_requests] ADD CONSTRAINT [FK_csr_requests_ob_consultation_id] FOREIGN KEY ([ob_consultation_id]) REFERENCES [dbo].[ob_consultations]([id]) ON DELETE SET NULL;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_csr_requests_or_schedule_id' AND parent_object_id = OBJECT_ID(N'[dbo].[csr_requests]'))
    ALTER TABLE [dbo].[csr_requests] ADD CONSTRAINT [FK_csr_requests_or_schedule_id] FOREIGN KEY ([or_schedule_id]) REFERENCES [dbo].[or_schedules]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_csr_requests_dr_schedule_id' AND parent_object_id = OBJECT_ID(N'[dbo].[csr_requests]'))
    ALTER TABLE [dbo].[csr_requests] ADD CONSTRAINT [FK_csr_requests_dr_schedule_id] FOREIGN KEY ([dr_schedule_id]) REFERENCES [dbo].[dr_schedules]([id]) ON DELETE SET NULL;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_csr_requests_requesting_department_id' AND parent_object_id = OBJECT_ID(N'[dbo].[csr_requests]'))
    ALTER TABLE [dbo].[csr_requests] ADD CONSTRAINT [FK_csr_requests_requesting_department_id] FOREIGN KEY ([requesting_department_id]) REFERENCES [dbo].[departments]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_csr_requests_requesting_doctor_id' AND parent_object_id = OBJECT_ID(N'[dbo].[csr_requests]'))
    ALTER TABLE [dbo].[csr_requests] ADD CONSTRAINT [FK_csr_requests_requesting_doctor_id] FOREIGN KEY ([requesting_doctor_id]) REFERENCES [dbo].[health_care_professionals]([id]) ON DELETE SET NULL;
-- csr_request_items
IF OBJECT_ID(N'[dbo].[csr_request_items]', N'U') IS NOT NULL
    DROP TABLE [dbo].[csr_request_items];
GO

CREATE TABLE [dbo].[csr_request_items] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [csr_request_id] BIGINT NOT NULL,
    [item_id] BIGINT NULL,
    [item_name] NVARCHAR(200) NOT NULL,
    [item_code] NVARCHAR(30) NOT NULL,
    [supply_unit_name] NVARCHAR(50) NOT NULL,
    [quantity] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [given_quantity] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [unit_cost] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [total_cost] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [status] NVARCHAR(30) NOT NULL DEFAULT (N'Pending'),
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_csr_request_items] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_csr_request_items_csr_request_id' AND object_id = OBJECT_ID(N'[dbo].[csr_request_items]'))
    CREATE NONCLUSTERED INDEX [IX_csr_request_items_csr_request_id] ON [dbo].[csr_request_items]([csr_request_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_csr_request_items_item_id' AND object_id = OBJECT_ID(N'[dbo].[csr_request_items]'))
    CREATE NONCLUSTERED INDEX [IX_csr_request_items_item_id] ON [dbo].[csr_request_items]([item_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_csr_request_items_history' AND object_id = OBJECT_ID(N'[dbo].[csr_request_items]'))
    CREATE NONCLUSTERED INDEX [IX_csr_request_items_history] ON [dbo].[csr_request_items]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_csr_request_items_is_active' AND object_id = OBJECT_ID(N'[dbo].[csr_request_items]'))
    CREATE NONCLUSTERED INDEX [IX_csr_request_items_is_active] ON [dbo].[csr_request_items]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_csr_request_items_created_date' AND object_id = OBJECT_ID(N'[dbo].[csr_request_items]'))
    CREATE NONCLUSTERED INDEX [IX_csr_request_items_created_date] ON [dbo].[csr_request_items]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_csr_request_items_status' AND object_id = OBJECT_ID(N'[dbo].[csr_request_items]'))
    CREATE NONCLUSTERED INDEX [IX_csr_request_items_status] ON [dbo].[csr_request_items]([status] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_csr_request_items_csr_request_id' AND parent_object_id = OBJECT_ID(N'[dbo].[csr_request_items]'))
    ALTER TABLE [dbo].[csr_request_items] ADD CONSTRAINT [FK_csr_request_items_csr_request_id] FOREIGN KEY ([csr_request_id]) REFERENCES [dbo].[csr_requests]([id]) ON DELETE CASCADE;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_csr_request_items_item_id' AND parent_object_id = OBJECT_ID(N'[dbo].[csr_request_items]'))
    ALTER TABLE [dbo].[csr_request_items] ADD CONSTRAINT [FK_csr_request_items_item_id] FOREIGN KEY ([item_id]) REFERENCES [dbo].[items]([id]) ON DELETE SET NULL;
-- radiology_requests
IF OBJECT_ID(N'[dbo].[radiology_requests]', N'U') IS NOT NULL
    DROP TABLE [dbo].[radiology_requests];
GO

CREATE TABLE [dbo].[radiology_requests] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [er_admission_id] BIGINT NULL,
    [opd_consultation_id] BIGINT NULL,
    [admission_id] BIGINT NULL,
    [dialysis_session_id] BIGINT NULL,
    [ob_consultation_id] BIGINT NULL,
    [or_schedule_id] BIGINT NULL,
    [dr_schedule_id] BIGINT NULL,
    [request_code] NVARCHAR(30) NOT NULL,
    [requesting_department_id] BIGINT NULL,
    [requesting_person] NVARCHAR(150) NULL,
    [requesting_doctor_id] BIGINT NULL,
    [status] NVARCHAR(30) NOT NULL DEFAULT (N'Pending'),
    [billing_status] NVARCHAR(30) NOT NULL DEFAULT (N'Pending'),
    [billing_id] BIGINT NULL,
    [total_amount] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [notes] NVARCHAR(MAX) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_radiology_requests] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_radiology_requests_request_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[radiology_requests]'))
    ALTER TABLE [dbo].[radiology_requests] ADD CONSTRAINT [UQ_radiology_requests_request_code__tenant_id__history] UNIQUE (request_code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_radiology_requests_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[radiology_requests]'))
    CREATE NONCLUSTERED INDEX [IX_radiology_requests_tenant_id] ON [dbo].[radiology_requests]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_radiology_requests_er_admission_id' AND object_id = OBJECT_ID(N'[dbo].[radiology_requests]'))
    CREATE NONCLUSTERED INDEX [IX_radiology_requests_er_admission_id] ON [dbo].[radiology_requests]([er_admission_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_radiology_requests_opd_consultation_id' AND object_id = OBJECT_ID(N'[dbo].[radiology_requests]'))
    CREATE NONCLUSTERED INDEX [IX_radiology_requests_opd_consultation_id] ON [dbo].[radiology_requests]([opd_consultation_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_radiology_requests_admission_id' AND object_id = OBJECT_ID(N'[dbo].[radiology_requests]'))
    CREATE NONCLUSTERED INDEX [IX_radiology_requests_admission_id] ON [dbo].[radiology_requests]([admission_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_radiology_requests_dialysis_session_id' AND object_id = OBJECT_ID(N'[dbo].[radiology_requests]'))
    CREATE NONCLUSTERED INDEX [IX_radiology_requests_dialysis_session_id] ON [dbo].[radiology_requests]([dialysis_session_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_radiology_requests_ob_consultation_id' AND object_id = OBJECT_ID(N'[dbo].[radiology_requests]'))
    CREATE NONCLUSTERED INDEX [IX_radiology_requests_ob_consultation_id] ON [dbo].[radiology_requests]([ob_consultation_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_radiology_requests_or_schedule_id' AND object_id = OBJECT_ID(N'[dbo].[radiology_requests]'))
    CREATE NONCLUSTERED INDEX [IX_radiology_requests_or_schedule_id] ON [dbo].[radiology_requests]([or_schedule_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_radiology_requests_dr_schedule_id' AND object_id = OBJECT_ID(N'[dbo].[radiology_requests]'))
    CREATE NONCLUSTERED INDEX [IX_radiology_requests_dr_schedule_id] ON [dbo].[radiology_requests]([dr_schedule_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_radiology_requests_requesting_department_id' AND object_id = OBJECT_ID(N'[dbo].[radiology_requests]'))
    CREATE NONCLUSTERED INDEX [IX_radiology_requests_requesting_department_id] ON [dbo].[radiology_requests]([requesting_department_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_radiology_requests_requesting_doctor_id' AND object_id = OBJECT_ID(N'[dbo].[radiology_requests]'))
    CREATE NONCLUSTERED INDEX [IX_radiology_requests_requesting_doctor_id] ON [dbo].[radiology_requests]([requesting_doctor_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_radiology_requests_history' AND object_id = OBJECT_ID(N'[dbo].[radiology_requests]'))
    CREATE NONCLUSTERED INDEX [IX_radiology_requests_history] ON [dbo].[radiology_requests]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_radiology_requests_is_active' AND object_id = OBJECT_ID(N'[dbo].[radiology_requests]'))
    CREATE NONCLUSTERED INDEX [IX_radiology_requests_is_active] ON [dbo].[radiology_requests]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_radiology_requests_created_date' AND object_id = OBJECT_ID(N'[dbo].[radiology_requests]'))
    CREATE NONCLUSTERED INDEX [IX_radiology_requests_created_date] ON [dbo].[radiology_requests]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_radiology_requests_status' AND object_id = OBJECT_ID(N'[dbo].[radiology_requests]'))
    CREATE NONCLUSTERED INDEX [IX_radiology_requests_status] ON [dbo].[radiology_requests]([status] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_radiology_requests_er_admission_id' AND parent_object_id = OBJECT_ID(N'[dbo].[radiology_requests]'))
    ALTER TABLE [dbo].[radiology_requests] ADD CONSTRAINT [FK_radiology_requests_er_admission_id] FOREIGN KEY ([er_admission_id]) REFERENCES [dbo].[er_admissions]([id]) ON DELETE SET NULL;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_radiology_requests_opd_consultation_id' AND parent_object_id = OBJECT_ID(N'[dbo].[radiology_requests]'))
    ALTER TABLE [dbo].[radiology_requests] ADD CONSTRAINT [FK_radiology_requests_opd_consultation_id] FOREIGN KEY ([opd_consultation_id]) REFERENCES [dbo].[opd_consultations]([id]) ON DELETE SET NULL;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_radiology_requests_admission_id' AND parent_object_id = OBJECT_ID(N'[dbo].[radiology_requests]'))
    ALTER TABLE [dbo].[radiology_requests] ADD CONSTRAINT [FK_radiology_requests_admission_id] FOREIGN KEY ([admission_id]) REFERENCES [dbo].[admissions]([id]) ON DELETE SET NULL;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_radiology_requests_dialysis_session_id' AND parent_object_id = OBJECT_ID(N'[dbo].[radiology_requests]'))
    ALTER TABLE [dbo].[radiology_requests] ADD CONSTRAINT [FK_radiology_requests_dialysis_session_id] FOREIGN KEY ([dialysis_session_id]) REFERENCES [dbo].[dialysis_sessions]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_radiology_requests_ob_consultation_id' AND parent_object_id = OBJECT_ID(N'[dbo].[radiology_requests]'))
    ALTER TABLE [dbo].[radiology_requests] ADD CONSTRAINT [FK_radiology_requests_ob_consultation_id] FOREIGN KEY ([ob_consultation_id]) REFERENCES [dbo].[ob_consultations]([id]) ON DELETE SET NULL;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_radiology_requests_or_schedule_id' AND parent_object_id = OBJECT_ID(N'[dbo].[radiology_requests]'))
    ALTER TABLE [dbo].[radiology_requests] ADD CONSTRAINT [FK_radiology_requests_or_schedule_id] FOREIGN KEY ([or_schedule_id]) REFERENCES [dbo].[or_schedules]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_radiology_requests_dr_schedule_id' AND parent_object_id = OBJECT_ID(N'[dbo].[radiology_requests]'))
    ALTER TABLE [dbo].[radiology_requests] ADD CONSTRAINT [FK_radiology_requests_dr_schedule_id] FOREIGN KEY ([dr_schedule_id]) REFERENCES [dbo].[dr_schedules]([id]) ON DELETE SET NULL;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_radiology_requests_requesting_department_id' AND parent_object_id = OBJECT_ID(N'[dbo].[radiology_requests]'))
    ALTER TABLE [dbo].[radiology_requests] ADD CONSTRAINT [FK_radiology_requests_requesting_department_id] FOREIGN KEY ([requesting_department_id]) REFERENCES [dbo].[departments]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_radiology_requests_requesting_doctor_id' AND parent_object_id = OBJECT_ID(N'[dbo].[radiology_requests]'))
    ALTER TABLE [dbo].[radiology_requests] ADD CONSTRAINT [FK_radiology_requests_requesting_doctor_id] FOREIGN KEY ([requesting_doctor_id]) REFERENCES [dbo].[health_care_professionals]([id]) ON DELETE SET NULL;
-- radiology_request_items
IF OBJECT_ID(N'[dbo].[radiology_request_items]', N'U') IS NOT NULL
    DROP TABLE [dbo].[radiology_request_items];
GO

CREATE TABLE [dbo].[radiology_request_items] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [radiology_request_id] BIGINT NOT NULL,
    [exam_type_id] BIGINT NULL,
    [exam_name] NVARCHAR(200) NOT NULL,
    [modality_name] NVARCHAR(100) NULL,
    [charge_amount] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [status] NVARCHAR(30) NOT NULL DEFAULT (N'Pending'),
    [result_text] NVARCHAR(MAX) NULL,
    [impression] NVARCHAR(MAX) NULL,
    [technique] NVARCHAR(MAX) NULL,
    [findings] NVARCHAR(MAX) NULL,
    [doctor_notes] NVARCHAR(MAX) NULL,
    [image_url] NVARCHAR(255) NULL,
    [result_date] DATETIME2 NULL,
    [radiologist_id] BIGINT NULL,
    [performed_by] BIGINT NULL,
    [performed_at] DATETIME2 NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_radiology_request_items] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_radiology_request_items_radiology_request_id' AND object_id = OBJECT_ID(N'[dbo].[radiology_request_items]'))
    CREATE NONCLUSTERED INDEX [IX_radiology_request_items_radiology_request_id] ON [dbo].[radiology_request_items]([radiology_request_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_radiology_request_items_exam_type_id' AND object_id = OBJECT_ID(N'[dbo].[radiology_request_items]'))
    CREATE NONCLUSTERED INDEX [IX_radiology_request_items_exam_type_id] ON [dbo].[radiology_request_items]([exam_type_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_radiology_request_items_radiologist_id' AND object_id = OBJECT_ID(N'[dbo].[radiology_request_items]'))
    CREATE NONCLUSTERED INDEX [IX_radiology_request_items_radiologist_id] ON [dbo].[radiology_request_items]([radiologist_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_radiology_request_items_performed_by' AND object_id = OBJECT_ID(N'[dbo].[radiology_request_items]'))
    CREATE NONCLUSTERED INDEX [IX_radiology_request_items_performed_by] ON [dbo].[radiology_request_items]([performed_by] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_radiology_request_items_history' AND object_id = OBJECT_ID(N'[dbo].[radiology_request_items]'))
    CREATE NONCLUSTERED INDEX [IX_radiology_request_items_history] ON [dbo].[radiology_request_items]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_radiology_request_items_is_active' AND object_id = OBJECT_ID(N'[dbo].[radiology_request_items]'))
    CREATE NONCLUSTERED INDEX [IX_radiology_request_items_is_active] ON [dbo].[radiology_request_items]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_radiology_request_items_created_date' AND object_id = OBJECT_ID(N'[dbo].[radiology_request_items]'))
    CREATE NONCLUSTERED INDEX [IX_radiology_request_items_created_date] ON [dbo].[radiology_request_items]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_radiology_request_items_status' AND object_id = OBJECT_ID(N'[dbo].[radiology_request_items]'))
    CREATE NONCLUSTERED INDEX [IX_radiology_request_items_status] ON [dbo].[radiology_request_items]([status] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_radiology_request_items_radiology_request_id' AND parent_object_id = OBJECT_ID(N'[dbo].[radiology_request_items]'))
    ALTER TABLE [dbo].[radiology_request_items] ADD CONSTRAINT [FK_radiology_request_items_radiology_request_id] FOREIGN KEY ([radiology_request_id]) REFERENCES [dbo].[radiology_requests]([id]) ON DELETE CASCADE;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_radiology_request_items_exam_type_id' AND parent_object_id = OBJECT_ID(N'[dbo].[radiology_request_items]'))
    ALTER TABLE [dbo].[radiology_request_items] ADD CONSTRAINT [FK_radiology_request_items_exam_type_id] FOREIGN KEY ([exam_type_id]) REFERENCES [dbo].[radiology_exam_types]([id]) ON DELETE SET NULL;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_radiology_request_items_radiologist_id' AND parent_object_id = OBJECT_ID(N'[dbo].[radiology_request_items]'))
    ALTER TABLE [dbo].[radiology_request_items] ADD CONSTRAINT [FK_radiology_request_items_radiologist_id] FOREIGN KEY ([radiologist_id]) REFERENCES [dbo].[health_care_professionals]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_radiology_request_items_performed_by' AND parent_object_id = OBJECT_ID(N'[dbo].[radiology_request_items]'))
    ALTER TABLE [dbo].[radiology_request_items] ADD CONSTRAINT [FK_radiology_request_items_performed_by] FOREIGN KEY ([performed_by]) REFERENCES [dbo].[users]([id]) ON DELETE SET NULL;
-- radiology_result_images
IF OBJECT_ID(N'[dbo].[radiology_result_images]', N'U') IS NOT NULL
    DROP TABLE [dbo].[radiology_result_images];
GO

CREATE TABLE [dbo].[radiology_result_images] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [radiology_request_item_id] BIGINT NOT NULL,
    [filename] NVARCHAR(255) NOT NULL,
    [original_name] NVARCHAR(255) NOT NULL,
    [path] NVARCHAR(500) NOT NULL,
    [mime_type] NVARCHAR(100) NULL,
    [file_size] INT NULL,
    [dicom_study_uid] NVARCHAR(255) NULL,
    [dicom_series_uid] NVARCHAR(255) NULL,
    [dicom_sop_uid] NVARCHAR(255) NULL,
    [is_primary] TINYINT NOT NULL DEFAULT (0),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_radiology_result_images] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_radiology_result_images_radiology_request_item_id' AND object_id = OBJECT_ID(N'[dbo].[radiology_result_images]'))
    CREATE NONCLUSTERED INDEX [IX_radiology_result_images_radiology_request_item_id] ON [dbo].[radiology_result_images]([radiology_request_item_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_radiology_result_images_history' AND object_id = OBJECT_ID(N'[dbo].[radiology_result_images]'))
    CREATE NONCLUSTERED INDEX [IX_radiology_result_images_history] ON [dbo].[radiology_result_images]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_radiology_result_images_created_date' AND object_id = OBJECT_ID(N'[dbo].[radiology_result_images]'))
    CREATE NONCLUSTERED INDEX [IX_radiology_result_images_created_date] ON [dbo].[radiology_result_images]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_radiology_result_images_radiology_request_item_id' AND parent_object_id = OBJECT_ID(N'[dbo].[radiology_result_images]'))
    ALTER TABLE [dbo].[radiology_result_images] ADD CONSTRAINT [FK_radiology_result_images_radiology_request_item_id] FOREIGN KEY ([radiology_request_item_id]) REFERENCES [dbo].[radiology_request_items]([id]) ON DELETE CASCADE;
-- pharmacy_requests
IF OBJECT_ID(N'[dbo].[pharmacy_requests]', N'U') IS NOT NULL
    DROP TABLE [dbo].[pharmacy_requests];
GO

CREATE TABLE [dbo].[pharmacy_requests] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [er_admission_id] BIGINT NULL,
    [opd_consultation_id] BIGINT NULL,
    [admission_id] BIGINT NULL,
    [dialysis_session_id] BIGINT NULL,
    [ob_consultation_id] BIGINT NULL,
    [or_schedule_id] BIGINT NULL,
    [dr_schedule_id] BIGINT NULL,
    [request_code] NVARCHAR(30) NOT NULL,
    [requesting_department_id] BIGINT NULL,
    [requesting_person] NVARCHAR(150) NULL,
    [requesting_doctor_id] BIGINT NULL,
    [priority] NVARCHAR(20) NOT NULL DEFAULT (N'Routine'),
    [status] NVARCHAR(30) NOT NULL DEFAULT (N'Pending'),
    [billing_status] NVARCHAR(30) NOT NULL DEFAULT (N'Pending'),
    [billing_id] BIGINT NULL,
    [total_amount] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [notes] NVARCHAR(MAX) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_pharmacy_requests] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_pharmacy_requests_request_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[pharmacy_requests]'))
    ALTER TABLE [dbo].[pharmacy_requests] ADD CONSTRAINT [UQ_pharmacy_requests_request_code__tenant_id__history] UNIQUE (request_code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_pharmacy_requests_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[pharmacy_requests]'))
    CREATE NONCLUSTERED INDEX [IX_pharmacy_requests_tenant_id] ON [dbo].[pharmacy_requests]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_pharmacy_requests_er_admission_id' AND object_id = OBJECT_ID(N'[dbo].[pharmacy_requests]'))
    CREATE NONCLUSTERED INDEX [IX_pharmacy_requests_er_admission_id] ON [dbo].[pharmacy_requests]([er_admission_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_pharmacy_requests_opd_consultation_id' AND object_id = OBJECT_ID(N'[dbo].[pharmacy_requests]'))
    CREATE NONCLUSTERED INDEX [IX_pharmacy_requests_opd_consultation_id] ON [dbo].[pharmacy_requests]([opd_consultation_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_pharmacy_requests_admission_id' AND object_id = OBJECT_ID(N'[dbo].[pharmacy_requests]'))
    CREATE NONCLUSTERED INDEX [IX_pharmacy_requests_admission_id] ON [dbo].[pharmacy_requests]([admission_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_pharmacy_requests_dialysis_session_id' AND object_id = OBJECT_ID(N'[dbo].[pharmacy_requests]'))
    CREATE NONCLUSTERED INDEX [IX_pharmacy_requests_dialysis_session_id] ON [dbo].[pharmacy_requests]([dialysis_session_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_pharmacy_requests_ob_consultation_id' AND object_id = OBJECT_ID(N'[dbo].[pharmacy_requests]'))
    CREATE NONCLUSTERED INDEX [IX_pharmacy_requests_ob_consultation_id] ON [dbo].[pharmacy_requests]([ob_consultation_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_pharmacy_requests_or_schedule_id' AND object_id = OBJECT_ID(N'[dbo].[pharmacy_requests]'))
    CREATE NONCLUSTERED INDEX [IX_pharmacy_requests_or_schedule_id] ON [dbo].[pharmacy_requests]([or_schedule_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_pharmacy_requests_dr_schedule_id' AND object_id = OBJECT_ID(N'[dbo].[pharmacy_requests]'))
    CREATE NONCLUSTERED INDEX [IX_pharmacy_requests_dr_schedule_id] ON [dbo].[pharmacy_requests]([dr_schedule_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_pharmacy_requests_requesting_department_id' AND object_id = OBJECT_ID(N'[dbo].[pharmacy_requests]'))
    CREATE NONCLUSTERED INDEX [IX_pharmacy_requests_requesting_department_id] ON [dbo].[pharmacy_requests]([requesting_department_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_pharmacy_requests_requesting_doctor_id' AND object_id = OBJECT_ID(N'[dbo].[pharmacy_requests]'))
    CREATE NONCLUSTERED INDEX [IX_pharmacy_requests_requesting_doctor_id] ON [dbo].[pharmacy_requests]([requesting_doctor_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_pharmacy_requests_history' AND object_id = OBJECT_ID(N'[dbo].[pharmacy_requests]'))
    CREATE NONCLUSTERED INDEX [IX_pharmacy_requests_history] ON [dbo].[pharmacy_requests]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_pharmacy_requests_is_active' AND object_id = OBJECT_ID(N'[dbo].[pharmacy_requests]'))
    CREATE NONCLUSTERED INDEX [IX_pharmacy_requests_is_active] ON [dbo].[pharmacy_requests]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_pharmacy_requests_created_date' AND object_id = OBJECT_ID(N'[dbo].[pharmacy_requests]'))
    CREATE NONCLUSTERED INDEX [IX_pharmacy_requests_created_date] ON [dbo].[pharmacy_requests]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_pharmacy_requests_status' AND object_id = OBJECT_ID(N'[dbo].[pharmacy_requests]'))
    CREATE NONCLUSTERED INDEX [IX_pharmacy_requests_status] ON [dbo].[pharmacy_requests]([status] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_pharmacy_requests_er_admission_id' AND parent_object_id = OBJECT_ID(N'[dbo].[pharmacy_requests]'))
    ALTER TABLE [dbo].[pharmacy_requests] ADD CONSTRAINT [FK_pharmacy_requests_er_admission_id] FOREIGN KEY ([er_admission_id]) REFERENCES [dbo].[er_admissions]([id]) ON DELETE SET NULL;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_pharmacy_requests_opd_consultation_id' AND parent_object_id = OBJECT_ID(N'[dbo].[pharmacy_requests]'))
    ALTER TABLE [dbo].[pharmacy_requests] ADD CONSTRAINT [FK_pharmacy_requests_opd_consultation_id] FOREIGN KEY ([opd_consultation_id]) REFERENCES [dbo].[opd_consultations]([id]) ON DELETE SET NULL;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_pharmacy_requests_admission_id' AND parent_object_id = OBJECT_ID(N'[dbo].[pharmacy_requests]'))
    ALTER TABLE [dbo].[pharmacy_requests] ADD CONSTRAINT [FK_pharmacy_requests_admission_id] FOREIGN KEY ([admission_id]) REFERENCES [dbo].[admissions]([id]) ON DELETE SET NULL;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_pharmacy_requests_dialysis_session_id' AND parent_object_id = OBJECT_ID(N'[dbo].[pharmacy_requests]'))
    ALTER TABLE [dbo].[pharmacy_requests] ADD CONSTRAINT [FK_pharmacy_requests_dialysis_session_id] FOREIGN KEY ([dialysis_session_id]) REFERENCES [dbo].[dialysis_sessions]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_pharmacy_requests_ob_consultation_id' AND parent_object_id = OBJECT_ID(N'[dbo].[pharmacy_requests]'))
    ALTER TABLE [dbo].[pharmacy_requests] ADD CONSTRAINT [FK_pharmacy_requests_ob_consultation_id] FOREIGN KEY ([ob_consultation_id]) REFERENCES [dbo].[ob_consultations]([id]) ON DELETE SET NULL;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_pharmacy_requests_or_schedule_id' AND parent_object_id = OBJECT_ID(N'[dbo].[pharmacy_requests]'))
    ALTER TABLE [dbo].[pharmacy_requests] ADD CONSTRAINT [FK_pharmacy_requests_or_schedule_id] FOREIGN KEY ([or_schedule_id]) REFERENCES [dbo].[or_schedules]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_pharmacy_requests_dr_schedule_id' AND parent_object_id = OBJECT_ID(N'[dbo].[pharmacy_requests]'))
    ALTER TABLE [dbo].[pharmacy_requests] ADD CONSTRAINT [FK_pharmacy_requests_dr_schedule_id] FOREIGN KEY ([dr_schedule_id]) REFERENCES [dbo].[dr_schedules]([id]) ON DELETE SET NULL;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_pharmacy_requests_requesting_department_id' AND parent_object_id = OBJECT_ID(N'[dbo].[pharmacy_requests]'))
    ALTER TABLE [dbo].[pharmacy_requests] ADD CONSTRAINT [FK_pharmacy_requests_requesting_department_id] FOREIGN KEY ([requesting_department_id]) REFERENCES [dbo].[departments]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_pharmacy_requests_requesting_doctor_id' AND parent_object_id = OBJECT_ID(N'[dbo].[pharmacy_requests]'))
    ALTER TABLE [dbo].[pharmacy_requests] ADD CONSTRAINT [FK_pharmacy_requests_requesting_doctor_id] FOREIGN KEY ([requesting_doctor_id]) REFERENCES [dbo].[health_care_professionals]([id]) ON DELETE NO ACTION;
-- pharmacy_request_items
IF OBJECT_ID(N'[dbo].[pharmacy_request_items]', N'U') IS NOT NULL
    DROP TABLE [dbo].[pharmacy_request_items];
GO

CREATE TABLE [dbo].[pharmacy_request_items] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [pharmacy_request_id] BIGINT NOT NULL,
    [item_id] BIGINT NULL,
    [drug_id] BIGINT NULL,
    [drug_batch_id] BIGINT NULL,
    [item_name] NVARCHAR(200) NOT NULL,
    [generic_name] NVARCHAR(200) NULL,
    [brand_name] NVARCHAR(200) NULL,
    [item_code] NVARCHAR(30) NULL,
    [philhealth_item_code] NVARCHAR(50) NULL,
    [supply_unit_name] NVARCHAR(100) NULL,
    [dosage] NVARCHAR(100) NULL,
    [dosage_form] NVARCHAR(50) NULL,
    [frequency] NVARCHAR(100) NULL,
    [route] NVARCHAR(100) NULL,
    [duration] NVARCHAR(100) NULL,
    [quantity] DECIMAL(12,2) NOT NULL DEFAULT (1),
    [given_quantity] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [unit_cost] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [total_cost] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [status] NVARCHAR(30) NOT NULL DEFAULT (N'Pending'),
    [dispensed_date] DATETIME2 NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_pharmacy_request_items] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_pharmacy_request_items_pharmacy_request_id' AND object_id = OBJECT_ID(N'[dbo].[pharmacy_request_items]'))
    CREATE NONCLUSTERED INDEX [IX_pharmacy_request_items_pharmacy_request_id] ON [dbo].[pharmacy_request_items]([pharmacy_request_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_pharmacy_request_items_item_id' AND object_id = OBJECT_ID(N'[dbo].[pharmacy_request_items]'))
    CREATE NONCLUSTERED INDEX [IX_pharmacy_request_items_item_id] ON [dbo].[pharmacy_request_items]([item_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_pharmacy_request_items_drug_id' AND object_id = OBJECT_ID(N'[dbo].[pharmacy_request_items]'))
    CREATE NONCLUSTERED INDEX [IX_pharmacy_request_items_drug_id] ON [dbo].[pharmacy_request_items]([drug_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_pharmacy_request_items_drug_batch_id' AND object_id = OBJECT_ID(N'[dbo].[pharmacy_request_items]'))
    CREATE NONCLUSTERED INDEX [IX_pharmacy_request_items_drug_batch_id] ON [dbo].[pharmacy_request_items]([drug_batch_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_pharmacy_request_items_history' AND object_id = OBJECT_ID(N'[dbo].[pharmacy_request_items]'))
    CREATE NONCLUSTERED INDEX [IX_pharmacy_request_items_history] ON [dbo].[pharmacy_request_items]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_pharmacy_request_items_is_active' AND object_id = OBJECT_ID(N'[dbo].[pharmacy_request_items]'))
    CREATE NONCLUSTERED INDEX [IX_pharmacy_request_items_is_active] ON [dbo].[pharmacy_request_items]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_pharmacy_request_items_created_date' AND object_id = OBJECT_ID(N'[dbo].[pharmacy_request_items]'))
    CREATE NONCLUSTERED INDEX [IX_pharmacy_request_items_created_date] ON [dbo].[pharmacy_request_items]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_pharmacy_request_items_status' AND object_id = OBJECT_ID(N'[dbo].[pharmacy_request_items]'))
    CREATE NONCLUSTERED INDEX [IX_pharmacy_request_items_status] ON [dbo].[pharmacy_request_items]([status] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_pharmacy_request_items_pharmacy_request_id' AND parent_object_id = OBJECT_ID(N'[dbo].[pharmacy_request_items]'))
    ALTER TABLE [dbo].[pharmacy_request_items] ADD CONSTRAINT [FK_pharmacy_request_items_pharmacy_request_id] FOREIGN KEY ([pharmacy_request_id]) REFERENCES [dbo].[pharmacy_requests]([id]) ON DELETE CASCADE;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_pharmacy_request_items_item_id' AND parent_object_id = OBJECT_ID(N'[dbo].[pharmacy_request_items]'))
    ALTER TABLE [dbo].[pharmacy_request_items] ADD CONSTRAINT [FK_pharmacy_request_items_item_id] FOREIGN KEY ([item_id]) REFERENCES [dbo].[items]([id]) ON DELETE SET NULL;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_pharmacy_request_items_drug_id' AND parent_object_id = OBJECT_ID(N'[dbo].[pharmacy_request_items]'))
    ALTER TABLE [dbo].[pharmacy_request_items] ADD CONSTRAINT [FK_pharmacy_request_items_drug_id] FOREIGN KEY ([drug_id]) REFERENCES [dbo].[drugs]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_pharmacy_request_items_drug_batch_id' AND parent_object_id = OBJECT_ID(N'[dbo].[pharmacy_request_items]'))
    ALTER TABLE [dbo].[pharmacy_request_items] ADD CONSTRAINT [FK_pharmacy_request_items_drug_batch_id] FOREIGN KEY ([drug_batch_id]) REFERENCES [dbo].[drug_batches]([id]) ON DELETE NO ACTION;
-- procedure_requests
IF OBJECT_ID(N'[dbo].[procedure_requests]', N'U') IS NOT NULL
    DROP TABLE [dbo].[procedure_requests];
GO

CREATE TABLE [dbo].[procedure_requests] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [request_code] NVARCHAR(30) NOT NULL,
    [er_admission_id] BIGINT NULL,
    [opd_consultation_id] BIGINT NULL,
    [admission_id] BIGINT NULL,
    [dialysis_session_id] BIGINT NULL,
    [ob_consultation_id] BIGINT NULL,
    [or_schedule_id] BIGINT NULL,
    [dr_schedule_id] BIGINT NULL,
    [requesting_department_id] BIGINT NULL,
    [requesting_doctor_id] BIGINT NULL,
    [status] NVARCHAR(30) NOT NULL DEFAULT (N'Pending'),
    [billing_status] NVARCHAR(30) NOT NULL DEFAULT (N'Pending'),
    [billing_id] BIGINT NULL,
    [total_amount] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [notes] NVARCHAR(MAX) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_procedure_requests] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_procedure_requests_request_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[procedure_requests]'))
    ALTER TABLE [dbo].[procedure_requests] ADD CONSTRAINT [UQ_procedure_requests_request_code__tenant_id__history] UNIQUE (request_code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_procedure_requests_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[procedure_requests]'))
    CREATE NONCLUSTERED INDEX [IX_procedure_requests_tenant_id] ON [dbo].[procedure_requests]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_procedure_requests_er_admission_id' AND object_id = OBJECT_ID(N'[dbo].[procedure_requests]'))
    CREATE NONCLUSTERED INDEX [IX_procedure_requests_er_admission_id] ON [dbo].[procedure_requests]([er_admission_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_procedure_requests_opd_consultation_id' AND object_id = OBJECT_ID(N'[dbo].[procedure_requests]'))
    CREATE NONCLUSTERED INDEX [IX_procedure_requests_opd_consultation_id] ON [dbo].[procedure_requests]([opd_consultation_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_procedure_requests_admission_id' AND object_id = OBJECT_ID(N'[dbo].[procedure_requests]'))
    CREATE NONCLUSTERED INDEX [IX_procedure_requests_admission_id] ON [dbo].[procedure_requests]([admission_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_procedure_requests_dialysis_session_id' AND object_id = OBJECT_ID(N'[dbo].[procedure_requests]'))
    CREATE NONCLUSTERED INDEX [IX_procedure_requests_dialysis_session_id] ON [dbo].[procedure_requests]([dialysis_session_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_procedure_requests_ob_consultation_id' AND object_id = OBJECT_ID(N'[dbo].[procedure_requests]'))
    CREATE NONCLUSTERED INDEX [IX_procedure_requests_ob_consultation_id] ON [dbo].[procedure_requests]([ob_consultation_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_procedure_requests_or_schedule_id' AND object_id = OBJECT_ID(N'[dbo].[procedure_requests]'))
    CREATE NONCLUSTERED INDEX [IX_procedure_requests_or_schedule_id] ON [dbo].[procedure_requests]([or_schedule_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_procedure_requests_dr_schedule_id' AND object_id = OBJECT_ID(N'[dbo].[procedure_requests]'))
    CREATE NONCLUSTERED INDEX [IX_procedure_requests_dr_schedule_id] ON [dbo].[procedure_requests]([dr_schedule_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_procedure_requests_requesting_department_id' AND object_id = OBJECT_ID(N'[dbo].[procedure_requests]'))
    CREATE NONCLUSTERED INDEX [IX_procedure_requests_requesting_department_id] ON [dbo].[procedure_requests]([requesting_department_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_procedure_requests_requesting_doctor_id' AND object_id = OBJECT_ID(N'[dbo].[procedure_requests]'))
    CREATE NONCLUSTERED INDEX [IX_procedure_requests_requesting_doctor_id] ON [dbo].[procedure_requests]([requesting_doctor_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_procedure_requests_history' AND object_id = OBJECT_ID(N'[dbo].[procedure_requests]'))
    CREATE NONCLUSTERED INDEX [IX_procedure_requests_history] ON [dbo].[procedure_requests]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_procedure_requests_is_active' AND object_id = OBJECT_ID(N'[dbo].[procedure_requests]'))
    CREATE NONCLUSTERED INDEX [IX_procedure_requests_is_active] ON [dbo].[procedure_requests]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_procedure_requests_created_date' AND object_id = OBJECT_ID(N'[dbo].[procedure_requests]'))
    CREATE NONCLUSTERED INDEX [IX_procedure_requests_created_date] ON [dbo].[procedure_requests]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_procedure_requests_status' AND object_id = OBJECT_ID(N'[dbo].[procedure_requests]'))
    CREATE NONCLUSTERED INDEX [IX_procedure_requests_status] ON [dbo].[procedure_requests]([status] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_procedure_requests_er_admission_id' AND parent_object_id = OBJECT_ID(N'[dbo].[procedure_requests]'))
    ALTER TABLE [dbo].[procedure_requests] ADD CONSTRAINT [FK_procedure_requests_er_admission_id] FOREIGN KEY ([er_admission_id]) REFERENCES [dbo].[er_admissions]([id]) ON DELETE SET NULL;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_procedure_requests_opd_consultation_id' AND parent_object_id = OBJECT_ID(N'[dbo].[procedure_requests]'))
    ALTER TABLE [dbo].[procedure_requests] ADD CONSTRAINT [FK_procedure_requests_opd_consultation_id] FOREIGN KEY ([opd_consultation_id]) REFERENCES [dbo].[opd_consultations]([id]) ON DELETE SET NULL;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_procedure_requests_admission_id' AND parent_object_id = OBJECT_ID(N'[dbo].[procedure_requests]'))
    ALTER TABLE [dbo].[procedure_requests] ADD CONSTRAINT [FK_procedure_requests_admission_id] FOREIGN KEY ([admission_id]) REFERENCES [dbo].[admissions]([id]) ON DELETE SET NULL;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_procedure_requests_dialysis_session_id' AND parent_object_id = OBJECT_ID(N'[dbo].[procedure_requests]'))
    ALTER TABLE [dbo].[procedure_requests] ADD CONSTRAINT [FK_procedure_requests_dialysis_session_id] FOREIGN KEY ([dialysis_session_id]) REFERENCES [dbo].[dialysis_sessions]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_procedure_requests_ob_consultation_id' AND parent_object_id = OBJECT_ID(N'[dbo].[procedure_requests]'))
    ALTER TABLE [dbo].[procedure_requests] ADD CONSTRAINT [FK_procedure_requests_ob_consultation_id] FOREIGN KEY ([ob_consultation_id]) REFERENCES [dbo].[ob_consultations]([id]) ON DELETE SET NULL;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_procedure_requests_or_schedule_id' AND parent_object_id = OBJECT_ID(N'[dbo].[procedure_requests]'))
    ALTER TABLE [dbo].[procedure_requests] ADD CONSTRAINT [FK_procedure_requests_or_schedule_id] FOREIGN KEY ([or_schedule_id]) REFERENCES [dbo].[or_schedules]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_procedure_requests_dr_schedule_id' AND parent_object_id = OBJECT_ID(N'[dbo].[procedure_requests]'))
    ALTER TABLE [dbo].[procedure_requests] ADD CONSTRAINT [FK_procedure_requests_dr_schedule_id] FOREIGN KEY ([dr_schedule_id]) REFERENCES [dbo].[dr_schedules]([id]) ON DELETE SET NULL;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_procedure_requests_requesting_department_id' AND parent_object_id = OBJECT_ID(N'[dbo].[procedure_requests]'))
    ALTER TABLE [dbo].[procedure_requests] ADD CONSTRAINT [FK_procedure_requests_requesting_department_id] FOREIGN KEY ([requesting_department_id]) REFERENCES [dbo].[departments]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_procedure_requests_requesting_doctor_id' AND parent_object_id = OBJECT_ID(N'[dbo].[procedure_requests]'))
    ALTER TABLE [dbo].[procedure_requests] ADD CONSTRAINT [FK_procedure_requests_requesting_doctor_id] FOREIGN KEY ([requesting_doctor_id]) REFERENCES [dbo].[health_care_professionals]([id]) ON DELETE NO ACTION;
-- procedure_request_items
IF OBJECT_ID(N'[dbo].[procedure_request_items]', N'U') IS NOT NULL
    DROP TABLE [dbo].[procedure_request_items];
GO

CREATE TABLE [dbo].[procedure_request_items] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [procedure_request_id] BIGINT NOT NULL,
    [procedure_id] BIGINT NULL,
    [procedure_name] NVARCHAR(200) NOT NULL,
    [category_name] NVARCHAR(100) NULL,
    [quantity] INT NOT NULL DEFAULT (1),
    [qty_given] INT NOT NULL DEFAULT (0),
    [unit_charge] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [total_charge] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [status] NVARCHAR(30) NOT NULL DEFAULT (N'Pending'),
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_procedure_request_items] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_procedure_request_items_procedure_request_id' AND object_id = OBJECT_ID(N'[dbo].[procedure_request_items]'))
    CREATE NONCLUSTERED INDEX [IX_procedure_request_items_procedure_request_id] ON [dbo].[procedure_request_items]([procedure_request_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_procedure_request_items_procedure_id' AND object_id = OBJECT_ID(N'[dbo].[procedure_request_items]'))
    CREATE NONCLUSTERED INDEX [IX_procedure_request_items_procedure_id] ON [dbo].[procedure_request_items]([procedure_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_procedure_request_items_history' AND object_id = OBJECT_ID(N'[dbo].[procedure_request_items]'))
    CREATE NONCLUSTERED INDEX [IX_procedure_request_items_history] ON [dbo].[procedure_request_items]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_procedure_request_items_is_active' AND object_id = OBJECT_ID(N'[dbo].[procedure_request_items]'))
    CREATE NONCLUSTERED INDEX [IX_procedure_request_items_is_active] ON [dbo].[procedure_request_items]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_procedure_request_items_created_date' AND object_id = OBJECT_ID(N'[dbo].[procedure_request_items]'))
    CREATE NONCLUSTERED INDEX [IX_procedure_request_items_created_date] ON [dbo].[procedure_request_items]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_procedure_request_items_status' AND object_id = OBJECT_ID(N'[dbo].[procedure_request_items]'))
    CREATE NONCLUSTERED INDEX [IX_procedure_request_items_status] ON [dbo].[procedure_request_items]([status] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_procedure_request_items_procedure_request_id' AND parent_object_id = OBJECT_ID(N'[dbo].[procedure_request_items]'))
    ALTER TABLE [dbo].[procedure_request_items] ADD CONSTRAINT [FK_procedure_request_items_procedure_request_id] FOREIGN KEY ([procedure_request_id]) REFERENCES [dbo].[procedure_requests]([id]) ON DELETE CASCADE;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_procedure_request_items_procedure_id' AND parent_object_id = OBJECT_ID(N'[dbo].[procedure_request_items]'))
    ALTER TABLE [dbo].[procedure_request_items] ADD CONSTRAINT [FK_procedure_request_items_procedure_id] FOREIGN KEY ([procedure_id]) REFERENCES [dbo].[procedures]([id]) ON DELETE SET NULL;
-- oecb_core_presentations
IF OBJECT_ID(N'[dbo].[oecb_core_presentations]', N'U') IS NOT NULL
    DROP TABLE [dbo].[oecb_core_presentations];
GO

CREATE TABLE [dbo].[oecb_core_presentations] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(200) NOT NULL,
    [description] NVARCHAR(MAX) NULL,
    [sort_order] INT NOT NULL DEFAULT (0),
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_oecb_core_presentations] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_oecb_core_presentations_code' AND object_id = OBJECT_ID(N'[dbo].[oecb_core_presentations]'))
    ALTER TABLE [dbo].[oecb_core_presentations] ADD CONSTRAINT [UQ_oecb_core_presentations_code] UNIQUE ([code]);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_oecb_core_presentations_history' AND object_id = OBJECT_ID(N'[dbo].[oecb_core_presentations]'))
    CREATE NONCLUSTERED INDEX [IX_oecb_core_presentations_history] ON [dbo].[oecb_core_presentations]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_oecb_core_presentations_is_active' AND object_id = OBJECT_ID(N'[dbo].[oecb_core_presentations]'))
    CREATE NONCLUSTERED INDEX [IX_oecb_core_presentations_is_active] ON [dbo].[oecb_core_presentations]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_oecb_core_presentations_created_date' AND object_id = OBJECT_ID(N'[dbo].[oecb_core_presentations]'))
    CREATE NONCLUSTERED INDEX [IX_oecb_core_presentations_created_date] ON [dbo].[oecb_core_presentations]([created_date] ASC);
-- oecb_fee_schedules
IF OBJECT_ID(N'[dbo].[oecb_fee_schedules]', N'U') IS NOT NULL
    DROP TABLE [dbo].[oecb_fee_schedules];
GO

CREATE TABLE [dbo].[oecb_fee_schedules] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [package_code] NVARCHAR(30) NOT NULL,
    [core_presentation_id] BIGINT NOT NULL,
    [description] NVARCHAR(MAX) NULL,
    [fixed_fee] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NULL,
    [updated_date] DATETIME2 NULL,
    [Package] CODE NULL,
    [OECB-001] DOB NULL,
    [OECB-002] CHP NULL,
    [OECB-003] AMS NULL,
    [OECB-004] SAP NULL,
    [OECB-005] SHD NULL,
    [OECB-006] FVR NULL,
    [OECB-007] TRA NULL,
    [OECB-008] PSN NULL,
    [OECB-009] DEH NULL,
    [OECB-010] SLZ NULL,
    [OECB-011] ALR NULL,
    [OECB-012] OBS NULL,
    [OECB-013] NEO NULL,
    [OECB-014] OTH NULL,
    CONSTRAINT [PK_oecb_fee_schedules] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_oecb_fee_schedules_package_code__core_presentation_id__history' AND object_id = OBJECT_ID(N'[dbo].[oecb_fee_schedules]'))
    ALTER TABLE [dbo].[oecb_fee_schedules] ADD CONSTRAINT [UQ_oecb_fee_schedules_package_code__core_presentation_id__history] UNIQUE (package_code, core_presentation_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_oecb_fee_schedules_core_presentation_id' AND object_id = OBJECT_ID(N'[dbo].[oecb_fee_schedules]'))
    CREATE NONCLUSTERED INDEX [IX_oecb_fee_schedules_core_presentation_id] ON [dbo].[oecb_fee_schedules]([core_presentation_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_oecb_fee_schedules_history' AND object_id = OBJECT_ID(N'[dbo].[oecb_fee_schedules]'))
    CREATE NONCLUSTERED INDEX [IX_oecb_fee_schedules_history] ON [dbo].[oecb_fee_schedules]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_oecb_fee_schedules_is_active' AND object_id = OBJECT_ID(N'[dbo].[oecb_fee_schedules]'))
    CREATE NONCLUSTERED INDEX [IX_oecb_fee_schedules_is_active] ON [dbo].[oecb_fee_schedules]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_oecb_fee_schedules_created_date' AND object_id = OBJECT_ID(N'[dbo].[oecb_fee_schedules]'))
    CREATE NONCLUSTERED INDEX [IX_oecb_fee_schedules_created_date] ON [dbo].[oecb_fee_schedules]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_oecb_fee_schedules_core_presentation_id' AND parent_object_id = OBJECT_ID(N'[dbo].[oecb_fee_schedules]'))
    ALTER TABLE [dbo].[oecb_fee_schedules] ADD CONSTRAINT [FK_oecb_fee_schedules_core_presentation_id] FOREIGN KEY ([core_presentation_id]) REFERENCES [dbo].[oecb_core_presentations]([id]) ON DELETE NO ACTION;
-- oecb_entries
IF OBJECT_ID(N'[dbo].[oecb_entries]', N'U') IS NOT NULL
    DROP TABLE [dbo].[oecb_entries];
GO

CREATE TABLE [dbo].[oecb_entries] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [er_admission_id] BIGINT NOT NULL,
    [core_presentation] NVARCHAR(200) NULL,
    [oecb_package_code] NVARCHAR(30) NULL,
    [oecb_fixed_fee] DECIMAL(12,2) NULL,
    [billing_id] BIGINT NULL,
    [billing_status] NVARCHAR(30) NOT NULL DEFAULT (N'Pending'),
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_oecb_entries] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_oecb_entries_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[oecb_entries]'))
    CREATE NONCLUSTERED INDEX [IX_oecb_entries_tenant_id] ON [dbo].[oecb_entries]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_oecb_entries_er_admission_id' AND object_id = OBJECT_ID(N'[dbo].[oecb_entries]'))
    CREATE NONCLUSTERED INDEX [IX_oecb_entries_er_admission_id] ON [dbo].[oecb_entries]([er_admission_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_oecb_entries_history' AND object_id = OBJECT_ID(N'[dbo].[oecb_entries]'))
    CREATE NONCLUSTERED INDEX [IX_oecb_entries_history] ON [dbo].[oecb_entries]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_oecb_entries_is_active' AND object_id = OBJECT_ID(N'[dbo].[oecb_entries]'))
    CREATE NONCLUSTERED INDEX [IX_oecb_entries_is_active] ON [dbo].[oecb_entries]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_oecb_entries_created_date' AND object_id = OBJECT_ID(N'[dbo].[oecb_entries]'))
    CREATE NONCLUSTERED INDEX [IX_oecb_entries_created_date] ON [dbo].[oecb_entries]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_oecb_entries_er_admission_id' AND parent_object_id = OBJECT_ID(N'[dbo].[oecb_entries]'))
    ALTER TABLE [dbo].[oecb_entries] ADD CONSTRAINT [FK_oecb_entries_er_admission_id] FOREIGN KEY ([er_admission_id]) REFERENCES [dbo].[er_admissions]([id]) ON DELETE CASCADE;
-- permissions
IF OBJECT_ID(N'[dbo].[permissions]', N'U') IS NOT NULL
    DROP TABLE [dbo].[permissions];
GO

CREATE TABLE [dbo].[permissions] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [display_name] NVARCHAR(200) NOT NULL,
    [module] NVARCHAR(100) NULL,
    [guard_name] NVARCHAR(40) NOT NULL DEFAULT (N'web'),
    [description] NVARCHAR(MAX) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_permissions] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_permissions_name' AND object_id = OBJECT_ID(N'[dbo].[permissions]'))
    ALTER TABLE [dbo].[permissions] ADD CONSTRAINT [UQ_permissions_name] UNIQUE ([name]);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_permissions_history' AND object_id = OBJECT_ID(N'[dbo].[permissions]'))
    CREATE NONCLUSTERED INDEX [IX_permissions_history] ON [dbo].[permissions]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_permissions_is_active' AND object_id = OBJECT_ID(N'[dbo].[permissions]'))
    CREATE NONCLUSTERED INDEX [IX_permissions_is_active] ON [dbo].[permissions]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_permissions_created_date' AND object_id = OBJECT_ID(N'[dbo].[permissions]'))
    CREATE NONCLUSTERED INDEX [IX_permissions_created_date] ON [dbo].[permissions]([created_date] ASC);
-- roles
IF OBJECT_ID(N'[dbo].[roles]', N'U') IS NOT NULL
    DROP TABLE [dbo].[roles];
GO

CREATE TABLE [dbo].[roles] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [name] NVARCHAR(40) NOT NULL,
    [display_name] NVARCHAR(100) NOT NULL,
    [description] NVARCHAR(MAX) NULL,
    [is_system] TINYINT NOT NULL DEFAULT (0),
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_roles] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_roles_name' AND object_id = OBJECT_ID(N'[dbo].[roles]'))
    ALTER TABLE [dbo].[roles] ADD CONSTRAINT [UQ_roles_name] UNIQUE ([name]);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_roles_history' AND object_id = OBJECT_ID(N'[dbo].[roles]'))
    CREATE NONCLUSTERED INDEX [IX_roles_history] ON [dbo].[roles]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_roles_is_active' AND object_id = OBJECT_ID(N'[dbo].[roles]'))
    CREATE NONCLUSTERED INDEX [IX_roles_is_active] ON [dbo].[roles]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_roles_created_date' AND object_id = OBJECT_ID(N'[dbo].[roles]'))
    CREATE NONCLUSTERED INDEX [IX_roles_created_date] ON [dbo].[roles]([created_date] ASC);
-- role_permissions
IF OBJECT_ID(N'[dbo].[role_permissions]', N'U') IS NOT NULL
    DROP TABLE [dbo].[role_permissions];
GO

CREATE TABLE [dbo].[role_permissions] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [role] NVARCHAR(40) NOT NULL,
    [permission_id] BIGINT NOT NULL,
    [tenant_id] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    CONSTRAINT [PK_role_permissions] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_role_permissions_role__permission_id__tenant_id' AND object_id = OBJECT_ID(N'[dbo].[role_permissions]'))
    ALTER TABLE [dbo].[role_permissions] ADD CONSTRAINT [UQ_role_permissions_role__permission_id__tenant_id] UNIQUE (role, permission_id, tenant_id);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_role_permissions_permission_id' AND object_id = OBJECT_ID(N'[dbo].[role_permissions]'))
    CREATE NONCLUSTERED INDEX [IX_role_permissions_permission_id] ON [dbo].[role_permissions]([permission_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_role_permissions_created_date' AND object_id = OBJECT_ID(N'[dbo].[role_permissions]'))
    CREATE NONCLUSTERED INDEX [IX_role_permissions_created_date] ON [dbo].[role_permissions]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_role_permissions_permission_id' AND parent_object_id = OBJECT_ID(N'[dbo].[role_permissions]'))
    ALTER TABLE [dbo].[role_permissions] ADD CONSTRAINT [FK_role_permissions_permission_id] FOREIGN KEY ([permission_id]) REFERENCES [dbo].[permissions]([id]) ON DELETE CASCADE;
-- user_permissions
IF OBJECT_ID(N'[dbo].[user_permissions]', N'U') IS NOT NULL
    DROP TABLE [dbo].[user_permissions];
GO

CREATE TABLE [dbo].[user_permissions] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [user_id] BIGINT NOT NULL,
    [permission_id] BIGINT NOT NULL,
    [type] NVARCHAR(10) NOT NULL,
    [tenant_id] BIGINT NULL,
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    CONSTRAINT [PK_user_permissions] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_user_permissions_user_id__permission_id__tenant_id' AND object_id = OBJECT_ID(N'[dbo].[user_permissions]'))
    ALTER TABLE [dbo].[user_permissions] ADD CONSTRAINT [UQ_user_permissions_user_id__permission_id__tenant_id] UNIQUE (user_id, permission_id, tenant_id);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_user_permissions_user_id' AND object_id = OBJECT_ID(N'[dbo].[user_permissions]'))
    CREATE NONCLUSTERED INDEX [IX_user_permissions_user_id] ON [dbo].[user_permissions]([user_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_user_permissions_permission_id' AND object_id = OBJECT_ID(N'[dbo].[user_permissions]'))
    CREATE NONCLUSTERED INDEX [IX_user_permissions_permission_id] ON [dbo].[user_permissions]([permission_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_user_permissions_created_date' AND object_id = OBJECT_ID(N'[dbo].[user_permissions]'))
    CREATE NONCLUSTERED INDEX [IX_user_permissions_created_date] ON [dbo].[user_permissions]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_user_permissions_user_id' AND parent_object_id = OBJECT_ID(N'[dbo].[user_permissions]'))
    ALTER TABLE [dbo].[user_permissions] ADD CONSTRAINT [FK_user_permissions_user_id] FOREIGN KEY ([user_id]) REFERENCES [dbo].[users]([id]) ON DELETE CASCADE;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_user_permissions_permission_id' AND parent_object_id = OBJECT_ID(N'[dbo].[user_permissions]'))
    ALTER TABLE [dbo].[user_permissions] ADD CONSTRAINT [FK_user_permissions_permission_id] FOREIGN KEY ([permission_id]) REFERENCES [dbo].[permissions]([id]) ON DELETE CASCADE;
-- permission_audit_log
IF OBJECT_ID(N'[dbo].[permission_audit_log]', N'U') IS NOT NULL
    DROP TABLE [dbo].[permission_audit_log];
GO

CREATE TABLE [dbo].[permission_audit_log] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [action] NVARCHAR(40) NOT NULL,
    [entity_type] NVARCHAR(60) NOT NULL,
    [entity_id] BIGINT NULL,
    [role] NVARCHAR(40) NULL,
    [user_id] BIGINT NULL,
    [permission_id] BIGINT NULL,
    [permission_name] NVARCHAR(100) NULL,
    [old_value] NVARCHAR(MAX) NULL,
    [new_value] NVARCHAR(MAX) NULL,
    [changed_by] BIGINT NOT NULL,
    [changed_date] DATETIME2 NOT NULL,
    [tenant_id] BIGINT NULL,
    [history] TINYINT NOT NULL DEFAULT (1),
    CONSTRAINT [PK_permission_audit_log] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_permission_audit_log_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[permission_audit_log]'))
    CREATE NONCLUSTERED INDEX [IX_permission_audit_log_tenant_id] ON [dbo].[permission_audit_log]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_permission_audit_log_history' AND object_id = OBJECT_ID(N'[dbo].[permission_audit_log]'))
    CREATE NONCLUSTERED INDEX [IX_permission_audit_log_history] ON [dbo].[permission_audit_log]([history] ASC);
-- fund_sources
IF OBJECT_ID(N'[dbo].[fund_sources]', N'U') IS NOT NULL
    DROP TABLE [dbo].[fund_sources];
GO

CREATE TABLE [dbo].[fund_sources] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [code] NVARCHAR(30) NOT NULL,
    [name] NVARCHAR(200) NOT NULL,
    [description] NVARCHAR(MAX) NULL,
    [budget_amount] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [used_amount] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_fund_sources] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_fund_sources_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[fund_sources]'))
    ALTER TABLE [dbo].[fund_sources] ADD CONSTRAINT [UQ_fund_sources_code__tenant_id__history] UNIQUE (code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_fund_sources_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[fund_sources]'))
    CREATE NONCLUSTERED INDEX [IX_fund_sources_tenant_id] ON [dbo].[fund_sources]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_fund_sources_history' AND object_id = OBJECT_ID(N'[dbo].[fund_sources]'))
    CREATE NONCLUSTERED INDEX [IX_fund_sources_history] ON [dbo].[fund_sources]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_fund_sources_is_active' AND object_id = OBJECT_ID(N'[dbo].[fund_sources]'))
    CREATE NONCLUSTERED INDEX [IX_fund_sources_is_active] ON [dbo].[fund_sources]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_fund_sources_created_date' AND object_id = OBJECT_ID(N'[dbo].[fund_sources]'))
    CREATE NONCLUSTERED INDEX [IX_fund_sources_created_date] ON [dbo].[fund_sources]([created_date] ASC);
-- billing_invoices
IF OBJECT_ID(N'[dbo].[billing_invoices]', N'U') IS NOT NULL
    DROP TABLE [dbo].[billing_invoices];
GO

CREATE TABLE [dbo].[billing_invoices] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [invoice_code] NVARCHAR(30) NOT NULL,
    [patient_id] BIGINT NOT NULL,
    [er_admission_id] BIGINT NULL,
    [opd_consultation_id] BIGINT NULL,
    [admission_id] BIGINT NULL,
    [dialysis_session_id] BIGINT NULL,
    [ob_consultation_id] BIGINT NULL,
    [or_schedule_id] BIGINT NULL,
    [dr_schedule_id] BIGINT NULL,
    [invoice_date] DATE NOT NULL,
    [total_amount] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [discount_type] NVARCHAR(20) NULL,
    [discount_value] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [discount_amount] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [paid_amount] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [balance] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [status] NVARCHAR(30) NOT NULL DEFAULT (N'Unpaid'),
    [notes] NVARCHAR(MAX) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_billing_invoices] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_billing_invoices_invoice_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[billing_invoices]'))
    ALTER TABLE [dbo].[billing_invoices] ADD CONSTRAINT [UQ_billing_invoices_invoice_code__tenant_id__history] UNIQUE (invoice_code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_billing_invoices_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[billing_invoices]'))
    CREATE NONCLUSTERED INDEX [IX_billing_invoices_tenant_id] ON [dbo].[billing_invoices]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_billing_invoices_patient_id' AND object_id = OBJECT_ID(N'[dbo].[billing_invoices]'))
    CREATE NONCLUSTERED INDEX [IX_billing_invoices_patient_id] ON [dbo].[billing_invoices]([patient_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_billing_invoices_er_admission_id' AND object_id = OBJECT_ID(N'[dbo].[billing_invoices]'))
    CREATE NONCLUSTERED INDEX [IX_billing_invoices_er_admission_id] ON [dbo].[billing_invoices]([er_admission_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_billing_invoices_opd_consultation_id' AND object_id = OBJECT_ID(N'[dbo].[billing_invoices]'))
    CREATE NONCLUSTERED INDEX [IX_billing_invoices_opd_consultation_id] ON [dbo].[billing_invoices]([opd_consultation_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_billing_invoices_admission_id' AND object_id = OBJECT_ID(N'[dbo].[billing_invoices]'))
    CREATE NONCLUSTERED INDEX [IX_billing_invoices_admission_id] ON [dbo].[billing_invoices]([admission_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_billing_invoices_dialysis_session_id' AND object_id = OBJECT_ID(N'[dbo].[billing_invoices]'))
    CREATE NONCLUSTERED INDEX [IX_billing_invoices_dialysis_session_id] ON [dbo].[billing_invoices]([dialysis_session_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_billing_invoices_ob_consultation_id' AND object_id = OBJECT_ID(N'[dbo].[billing_invoices]'))
    CREATE NONCLUSTERED INDEX [IX_billing_invoices_ob_consultation_id] ON [dbo].[billing_invoices]([ob_consultation_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_billing_invoices_or_schedule_id' AND object_id = OBJECT_ID(N'[dbo].[billing_invoices]'))
    CREATE NONCLUSTERED INDEX [IX_billing_invoices_or_schedule_id] ON [dbo].[billing_invoices]([or_schedule_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_billing_invoices_dr_schedule_id' AND object_id = OBJECT_ID(N'[dbo].[billing_invoices]'))
    CREATE NONCLUSTERED INDEX [IX_billing_invoices_dr_schedule_id] ON [dbo].[billing_invoices]([dr_schedule_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_billing_invoices_history' AND object_id = OBJECT_ID(N'[dbo].[billing_invoices]'))
    CREATE NONCLUSTERED INDEX [IX_billing_invoices_history] ON [dbo].[billing_invoices]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_billing_invoices_is_active' AND object_id = OBJECT_ID(N'[dbo].[billing_invoices]'))
    CREATE NONCLUSTERED INDEX [IX_billing_invoices_is_active] ON [dbo].[billing_invoices]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_billing_invoices_created_date' AND object_id = OBJECT_ID(N'[dbo].[billing_invoices]'))
    CREATE NONCLUSTERED INDEX [IX_billing_invoices_created_date] ON [dbo].[billing_invoices]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_billing_invoices_status' AND object_id = OBJECT_ID(N'[dbo].[billing_invoices]'))
    CREATE NONCLUSTERED INDEX [IX_billing_invoices_status] ON [dbo].[billing_invoices]([status] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_billing_invoices_patient_id' AND parent_object_id = OBJECT_ID(N'[dbo].[billing_invoices]'))
    ALTER TABLE [dbo].[billing_invoices] ADD CONSTRAINT [FK_billing_invoices_patient_id] FOREIGN KEY ([patient_id]) REFERENCES [dbo].[patients]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_billing_invoices_er_admission_id' AND parent_object_id = OBJECT_ID(N'[dbo].[billing_invoices]'))
    ALTER TABLE [dbo].[billing_invoices] ADD CONSTRAINT [FK_billing_invoices_er_admission_id] FOREIGN KEY ([er_admission_id]) REFERENCES [dbo].[er_admissions]([id]) ON DELETE SET NULL;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_billing_invoices_opd_consultation_id' AND parent_object_id = OBJECT_ID(N'[dbo].[billing_invoices]'))
    ALTER TABLE [dbo].[billing_invoices] ADD CONSTRAINT [FK_billing_invoices_opd_consultation_id] FOREIGN KEY ([opd_consultation_id]) REFERENCES [dbo].[opd_consultations]([id]) ON DELETE SET NULL;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_billing_invoices_admission_id' AND parent_object_id = OBJECT_ID(N'[dbo].[billing_invoices]'))
    ALTER TABLE [dbo].[billing_invoices] ADD CONSTRAINT [FK_billing_invoices_admission_id] FOREIGN KEY ([admission_id]) REFERENCES [dbo].[admissions]([id]) ON DELETE SET NULL;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_billing_invoices_dialysis_session_id' AND parent_object_id = OBJECT_ID(N'[dbo].[billing_invoices]'))
    ALTER TABLE [dbo].[billing_invoices] ADD CONSTRAINT [FK_billing_invoices_dialysis_session_id] FOREIGN KEY ([dialysis_session_id]) REFERENCES [dbo].[dialysis_sessions]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_billing_invoices_ob_consultation_id' AND parent_object_id = OBJECT_ID(N'[dbo].[billing_invoices]'))
    ALTER TABLE [dbo].[billing_invoices] ADD CONSTRAINT [FK_billing_invoices_ob_consultation_id] FOREIGN KEY ([ob_consultation_id]) REFERENCES [dbo].[ob_consultations]([id]) ON DELETE SET NULL;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_billing_invoices_or_schedule_id' AND parent_object_id = OBJECT_ID(N'[dbo].[billing_invoices]'))
    ALTER TABLE [dbo].[billing_invoices] ADD CONSTRAINT [FK_billing_invoices_or_schedule_id] FOREIGN KEY ([or_schedule_id]) REFERENCES [dbo].[or_schedules]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_billing_invoices_dr_schedule_id' AND parent_object_id = OBJECT_ID(N'[dbo].[billing_invoices]'))
    ALTER TABLE [dbo].[billing_invoices] ADD CONSTRAINT [FK_billing_invoices_dr_schedule_id] FOREIGN KEY ([dr_schedule_id]) REFERENCES [dbo].[dr_schedules]([id]) ON DELETE SET NULL;
-- billing_invoice_items
IF OBJECT_ID(N'[dbo].[billing_invoice_items]', N'U') IS NOT NULL
    DROP TABLE [dbo].[billing_invoice_items];
GO

CREATE TABLE [dbo].[billing_invoice_items] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [billing_invoice_id] BIGINT NOT NULL,
    [source_type] NVARCHAR(30) NOT NULL,
    [source_id] BIGINT NULL,
    [source_request_code] NVARCHAR(30) NULL,
    [description] NVARCHAR(255) NOT NULL,
    [quantity] DECIMAL(12,2) NOT NULL DEFAULT (1),
    [unit_price] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [total_price] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_billing_invoice_items] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_billing_invoice_items_billing_invoice_id' AND object_id = OBJECT_ID(N'[dbo].[billing_invoice_items]'))
    CREATE NONCLUSTERED INDEX [IX_billing_invoice_items_billing_invoice_id] ON [dbo].[billing_invoice_items]([billing_invoice_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_billing_invoice_items_history' AND object_id = OBJECT_ID(N'[dbo].[billing_invoice_items]'))
    CREATE NONCLUSTERED INDEX [IX_billing_invoice_items_history] ON [dbo].[billing_invoice_items]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_billing_invoice_items_is_active' AND object_id = OBJECT_ID(N'[dbo].[billing_invoice_items]'))
    CREATE NONCLUSTERED INDEX [IX_billing_invoice_items_is_active] ON [dbo].[billing_invoice_items]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_billing_invoice_items_created_date' AND object_id = OBJECT_ID(N'[dbo].[billing_invoice_items]'))
    CREATE NONCLUSTERED INDEX [IX_billing_invoice_items_created_date] ON [dbo].[billing_invoice_items]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_billing_invoice_items_billing_invoice_id' AND parent_object_id = OBJECT_ID(N'[dbo].[billing_invoice_items]'))
    ALTER TABLE [dbo].[billing_invoice_items] ADD CONSTRAINT [FK_billing_invoice_items_billing_invoice_id] FOREIGN KEY ([billing_invoice_id]) REFERENCES [dbo].[billing_invoices]([id]) ON DELETE CASCADE;
-- billing_payments
IF OBJECT_ID(N'[dbo].[billing_payments]', N'U') IS NOT NULL
    DROP TABLE [dbo].[billing_payments];
GO

CREATE TABLE [dbo].[billing_payments] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [billing_invoice_id] BIGINT NULL,
    [payment_code] NVARCHAR(30) NOT NULL,
    [payment_date] DATE NOT NULL,
    [amount] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [payment_method] NVARCHAR(50) NOT NULL DEFAULT (N'Cash'),
    [fund_source_id] BIGINT NULL,
    [reference_no] NVARCHAR(100) NULL,
    [notes] NVARCHAR(MAX) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_billing_payments] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_billing_payments_payment_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[billing_payments]'))
    ALTER TABLE [dbo].[billing_payments] ADD CONSTRAINT [UQ_billing_payments_payment_code__tenant_id__history] UNIQUE (payment_code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_billing_payments_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[billing_payments]'))
    CREATE NONCLUSTERED INDEX [IX_billing_payments_tenant_id] ON [dbo].[billing_payments]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_billing_payments_billing_invoice_id' AND object_id = OBJECT_ID(N'[dbo].[billing_payments]'))
    CREATE NONCLUSTERED INDEX [IX_billing_payments_billing_invoice_id] ON [dbo].[billing_payments]([billing_invoice_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_billing_payments_fund_source_id' AND object_id = OBJECT_ID(N'[dbo].[billing_payments]'))
    CREATE NONCLUSTERED INDEX [IX_billing_payments_fund_source_id] ON [dbo].[billing_payments]([fund_source_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_billing_payments_history' AND object_id = OBJECT_ID(N'[dbo].[billing_payments]'))
    CREATE NONCLUSTERED INDEX [IX_billing_payments_history] ON [dbo].[billing_payments]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_billing_payments_is_active' AND object_id = OBJECT_ID(N'[dbo].[billing_payments]'))
    CREATE NONCLUSTERED INDEX [IX_billing_payments_is_active] ON [dbo].[billing_payments]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_billing_payments_created_date' AND object_id = OBJECT_ID(N'[dbo].[billing_payments]'))
    CREATE NONCLUSTERED INDEX [IX_billing_payments_created_date] ON [dbo].[billing_payments]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_billing_payments_billing_invoice_id' AND parent_object_id = OBJECT_ID(N'[dbo].[billing_payments]'))
    ALTER TABLE [dbo].[billing_payments] ADD CONSTRAINT [FK_billing_payments_billing_invoice_id] FOREIGN KEY ([billing_invoice_id]) REFERENCES [dbo].[billing_invoices]([id]) ON DELETE CASCADE;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_billing_payments_fund_source_id' AND parent_object_id = OBJECT_ID(N'[dbo].[billing_payments]'))
    ALTER TABLE [dbo].[billing_payments] ADD CONSTRAINT [FK_billing_payments_fund_source_id] FOREIGN KEY ([fund_source_id]) REFERENCES [dbo].[fund_sources]([id]) ON DELETE SET NULL;
-- billing_payments: patient/encounter columns (2026_07_15_000002)
IF NOT EXISTS (SELECT * FROM sys.columns WHERE name = N'patient_id' AND object_id = OBJECT_ID(N'[dbo].[billing_payments]'))
    ALTER TABLE [dbo].[billing_payments] ADD [patient_id] BIGINT NULL;
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_billing_payments_patient_id' AND object_id = OBJECT_ID(N'[dbo].[billing_payments]'))
    CREATE NONCLUSTERED INDEX [IX_billing_payments_patient_id] ON [dbo].[billing_payments]([patient_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.columns WHERE name = N'admission_id' AND object_id = OBJECT_ID(N'[dbo].[billing_payments]'))
    ALTER TABLE [dbo].[billing_payments] ADD [admission_id] BIGINT NULL;
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_billing_payments_admission_id' AND object_id = OBJECT_ID(N'[dbo].[billing_payments]'))
    CREATE NONCLUSTERED INDEX [IX_billing_payments_admission_id] ON [dbo].[billing_payments]([admission_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.columns WHERE name = N'er_admission_id' AND object_id = OBJECT_ID(N'[dbo].[billing_payments]'))
    ALTER TABLE [dbo].[billing_payments] ADD [er_admission_id] BIGINT NULL;
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_billing_payments_er_admission_id' AND object_id = OBJECT_ID(N'[dbo].[billing_payments]'))
    CREATE NONCLUSTERED INDEX [IX_billing_payments_er_admission_id] ON [dbo].[billing_payments]([er_admission_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.columns WHERE name = N'opd_consultation_id' AND object_id = OBJECT_ID(N'[dbo].[billing_payments]'))
    ALTER TABLE [dbo].[billing_payments] ADD [opd_consultation_id] BIGINT NULL;
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_billing_payments_opd_consultation_id' AND object_id = OBJECT_ID(N'[dbo].[billing_payments]'))
    CREATE NONCLUSTERED INDEX [IX_billing_payments_opd_consultation_id] ON [dbo].[billing_payments]([opd_consultation_id] ASC);
-- billing_payments: make billing_invoice_id nullable for standalone payments (2026_07_15_000003)
IF EXISTS (SELECT * FROM sys.columns WHERE name = N'billing_invoice_id' AND object_id = OBJECT_ID(N'[dbo].[billing_payments]') AND is_nullable = 0)
    ALTER TABLE [dbo].[billing_payments] ALTER COLUMN [billing_invoice_id] BIGINT NULL;
-- billing_manual_charges
IF OBJECT_ID(N'[dbo].[billing_manual_charges]', N'U') IS NOT NULL
    DROP TABLE [dbo].[billing_manual_charges];
GO

CREATE TABLE [dbo].[billing_manual_charges] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [billing_invoice_id] BIGINT NULL,
    [patient_id] BIGINT NULL,
    [charge_type] NVARCHAR(50) NOT NULL DEFAULT (N'Miscellaneous'),
    [description] NVARCHAR(255) NOT NULL,
    [quantity] DECIMAL(12,2) NOT NULL DEFAULT (1),
    [unit_price] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [total_price] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [notes] NVARCHAR(MAX) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_billing_manual_charges] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_billing_manual_charges_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[billing_manual_charges]'))
    CREATE NONCLUSTERED INDEX [IX_billing_manual_charges_tenant_id] ON [dbo].[billing_manual_charges]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_billing_manual_charges_billing_invoice_id' AND object_id = OBJECT_ID(N'[dbo].[billing_manual_charges]'))
    CREATE NONCLUSTERED INDEX [IX_billing_manual_charges_billing_invoice_id] ON [dbo].[billing_manual_charges]([billing_invoice_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_billing_manual_charges_history' AND object_id = OBJECT_ID(N'[dbo].[billing_manual_charges]'))
    CREATE NONCLUSTERED INDEX [IX_billing_manual_charges_history] ON [dbo].[billing_manual_charges]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_billing_manual_charges_is_active' AND object_id = OBJECT_ID(N'[dbo].[billing_manual_charges]'))
    CREATE NONCLUSTERED INDEX [IX_billing_manual_charges_is_active] ON [dbo].[billing_manual_charges]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_billing_manual_charges_created_date' AND object_id = OBJECT_ID(N'[dbo].[billing_manual_charges]'))
    CREATE NONCLUSTERED INDEX [IX_billing_manual_charges_created_date] ON [dbo].[billing_manual_charges]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'idx_billing_manual_charges_patient_id' AND object_id = OBJECT_ID(N'[dbo].[billing_manual_charges]'))
    CREATE NONCLUSTERED INDEX [idx_billing_manual_charges_patient_id] ON [dbo].[billing_manual_charges]([patient_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'idx_billing_manual_charges_invoice_null' AND object_id = OBJECT_ID(N'[dbo].[billing_manual_charges]'))
    CREATE NONCLUSTERED INDEX [idx_billing_manual_charges_invoice_null] ON [dbo].[billing_manual_charges]([billing_invoice_id] ASC) WHERE [billing_invoice_id] IS NULL;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_billing_manual_charges_billing_invoice_id' AND parent_object_id = OBJECT_ID(N'[dbo].[billing_manual_charges]'))
    ALTER TABLE [dbo].[billing_manual_charges] ADD CONSTRAINT [FK_billing_manual_charges_billing_invoice_id] FOREIGN KEY ([billing_invoice_id]) REFERENCES [dbo].[billing_invoices]([id]) ON DELETE CASCADE;
-- patient_account_entries (2026_07_15_000001)
IF OBJECT_ID(N'[dbo].[patient_account_entries]', N'U') IS NOT NULL
    DROP TABLE [dbo].[patient_account_entries];
GO

CREATE TABLE [dbo].[patient_account_entries] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [patient_id] BIGINT NOT NULL,
    [admission_id] BIGINT NULL,
    [er_admission_id] BIGINT NULL,
    [opd_consultation_id] BIGINT NULL,
    [or_schedule_id] BIGINT NULL,
    [dr_schedule_id] BIGINT NULL,
    [ob_consultation_id] BIGINT NULL,
    [dialysis_session_id] BIGINT NULL,
    [entry_type] NVARCHAR(20) NOT NULL,
    [source_type] NVARCHAR(30) NOT NULL,
    [source_id] BIGINT NULL,
    [source_code] NVARCHAR(30) NULL,
    [debit] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [credit] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [balance] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [description] NVARCHAR(MAX) NULL,
    [notes] NVARCHAR(MAX) NULL,
    [billing_invoice_id] BIGINT NULL,
    [fund_source_id] BIGINT NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_patient_account_entries] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_patient_account_entries_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[patient_account_entries]'))
    CREATE NONCLUSTERED INDEX [IX_patient_account_entries_tenant_id] ON [dbo].[patient_account_entries]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_patient_account_entries_patient_id' AND object_id = OBJECT_ID(N'[dbo].[patient_account_entries]'))
    CREATE NONCLUSTERED INDEX [IX_patient_account_entries_patient_id] ON [dbo].[patient_account_entries]([patient_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_patient_account_entries_admission_id' AND object_id = OBJECT_ID(N'[dbo].[patient_account_entries]'))
    CREATE NONCLUSTERED INDEX [IX_patient_account_entries_admission_id] ON [dbo].[patient_account_entries]([admission_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_patient_account_entries_er_admission_id' AND object_id = OBJECT_ID(N'[dbo].[patient_account_entries]'))
    CREATE NONCLUSTERED INDEX [IX_patient_account_entries_er_admission_id] ON [dbo].[patient_account_entries]([er_admission_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_patient_account_entries_opd_consultation_id' AND object_id = OBJECT_ID(N'[dbo].[patient_account_entries]'))
    CREATE NONCLUSTERED INDEX [IX_patient_account_entries_opd_consultation_id] ON [dbo].[patient_account_entries]([opd_consultation_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_patient_account_entries_or_schedule_id' AND object_id = OBJECT_ID(N'[dbo].[patient_account_entries]'))
    CREATE NONCLUSTERED INDEX [IX_patient_account_entries_or_schedule_id] ON [dbo].[patient_account_entries]([or_schedule_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_patient_account_entries_dr_schedule_id' AND object_id = OBJECT_ID(N'[dbo].[patient_account_entries]'))
    CREATE NONCLUSTERED INDEX [IX_patient_account_entries_dr_schedule_id] ON [dbo].[patient_account_entries]([dr_schedule_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_patient_account_entries_ob_consultation_id' AND object_id = OBJECT_ID(N'[dbo].[patient_account_entries]'))
    CREATE NONCLUSTERED INDEX [IX_patient_account_entries_ob_consultation_id] ON [dbo].[patient_account_entries]([ob_consultation_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_patient_account_entries_dialysis_session_id' AND object_id = OBJECT_ID(N'[dbo].[patient_account_entries]'))
    CREATE NONCLUSTERED INDEX [IX_patient_account_entries_dialysis_session_id] ON [dbo].[patient_account_entries]([dialysis_session_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_patient_account_entries_billing_invoice_id' AND object_id = OBJECT_ID(N'[dbo].[patient_account_entries]'))
    CREATE NONCLUSTERED INDEX [IX_patient_account_entries_billing_invoice_id] ON [dbo].[patient_account_entries]([billing_invoice_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_patient_account_entries_fund_source_id' AND object_id = OBJECT_ID(N'[dbo].[patient_account_entries]'))
    CREATE NONCLUSTERED INDEX [IX_patient_account_entries_fund_source_id] ON [dbo].[patient_account_entries]([fund_source_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_patient_account_entries_history' AND object_id = OBJECT_ID(N'[dbo].[patient_account_entries]'))
    CREATE NONCLUSTERED INDEX [IX_patient_account_entries_history] ON [dbo].[patient_account_entries]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_patient_account_entries_is_active' AND object_id = OBJECT_ID(N'[dbo].[patient_account_entries]'))
    CREATE NONCLUSTERED INDEX [IX_patient_account_entries_is_active] ON [dbo].[patient_account_entries]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_patient_account_entries_created_date' AND object_id = OBJECT_ID(N'[dbo].[patient_account_entries]'))
    CREATE NONCLUSTERED INDEX [IX_patient_account_entries_created_date] ON [dbo].[patient_account_entries]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_patient_account_entries_billing_invoice_id' AND parent_object_id = OBJECT_ID(N'[dbo].[patient_account_entries]'))
    ALTER TABLE [dbo].[patient_account_entries] ADD CONSTRAINT [FK_patient_account_entries_billing_invoice_id] FOREIGN KEY ([billing_invoice_id]) REFERENCES [dbo].[billing_invoices]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_patient_account_entries_fund_source_id' AND parent_object_id = OBJECT_ID(N'[dbo].[patient_account_entries]'))
    ALTER TABLE [dbo].[patient_account_entries] ADD CONSTRAINT [FK_patient_account_entries_fund_source_id] FOREIGN KEY ([fund_source_id]) REFERENCES [dbo].[fund_sources]([id]) ON DELETE NO ACTION;
-- claim_rejections
IF OBJECT_ID(N'[dbo].[claim_rejections]', N'U') IS NOT NULL
    DROP TABLE [dbo].[claim_rejections];
GO

CREATE TABLE [dbo].[claim_rejections] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [philhealth_claim_id] BIGINT NOT NULL,
    [rejection_code] NVARCHAR(50) NULL,
    [rejection_reason] NVARCHAR(MAX) NULL,
    [rejection_date] DATE NULL,
    [resubmission_status] NVARCHAR(30) NOT NULL DEFAULT (N'Pending'),
    [resubmitted_at] DATETIME2 NULL,
    [resolution_notes] NVARCHAR(MAX) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_claim_rejections] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_claim_rejections_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[claim_rejections]'))
    CREATE NONCLUSTERED INDEX [IX_claim_rejections_tenant_id] ON [dbo].[claim_rejections]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_claim_rejections_philhealth_claim_id' AND object_id = OBJECT_ID(N'[dbo].[claim_rejections]'))
    CREATE NONCLUSTERED INDEX [IX_claim_rejections_philhealth_claim_id] ON [dbo].[claim_rejections]([philhealth_claim_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_claim_rejections_history' AND object_id = OBJECT_ID(N'[dbo].[claim_rejections]'))
    CREATE NONCLUSTERED INDEX [IX_claim_rejections_history] ON [dbo].[claim_rejections]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_claim_rejections_is_active' AND object_id = OBJECT_ID(N'[dbo].[claim_rejections]'))
    CREATE NONCLUSTERED INDEX [IX_claim_rejections_is_active] ON [dbo].[claim_rejections]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_claim_rejections_created_date' AND object_id = OBJECT_ID(N'[dbo].[claim_rejections]'))
    CREATE NONCLUSTERED INDEX [IX_claim_rejections_created_date] ON [dbo].[claim_rejections]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_claim_rejections_philhealth_claim_id' AND parent_object_id = OBJECT_ID(N'[dbo].[claim_rejections]'))
    ALTER TABLE [dbo].[claim_rejections] ADD CONSTRAINT [FK_claim_rejections_philhealth_claim_id] FOREIGN KEY ([philhealth_claim_id]) REFERENCES [dbo].[philhealth_claims]([id]) ON DELETE NO ACTION;
-- claim_status_polls
IF OBJECT_ID(N'[dbo].[claim_status_polls]', N'U') IS NOT NULL
    DROP TABLE [dbo].[claim_status_polls];
GO

CREATE TABLE [dbo].[claim_status_polls] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [philhealth_claim_id] BIGINT NULL,
    [claim_batch_id] BIGINT NULL,
    [polled_at] DATETIME2 NOT NULL,
    [status_before] NVARCHAR(30) NULL,
    [status_after] NVARCHAR(30) NULL,
    [response_data] NVARCHAR(MAX) NULL,
    [poll_source] NVARCHAR(20) NOT NULL DEFAULT (N'manual'),
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_claim_status_polls] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_claim_status_polls_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[claim_status_polls]'))
    CREATE NONCLUSTERED INDEX [IX_claim_status_polls_tenant_id] ON [dbo].[claim_status_polls]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_claim_status_polls_philhealth_claim_id' AND object_id = OBJECT_ID(N'[dbo].[claim_status_polls]'))
    CREATE NONCLUSTERED INDEX [IX_claim_status_polls_philhealth_claim_id] ON [dbo].[claim_status_polls]([philhealth_claim_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_claim_status_polls_claim_batch_id' AND object_id = OBJECT_ID(N'[dbo].[claim_status_polls]'))
    CREATE NONCLUSTERED INDEX [IX_claim_status_polls_claim_batch_id] ON [dbo].[claim_status_polls]([claim_batch_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_claim_status_polls_history' AND object_id = OBJECT_ID(N'[dbo].[claim_status_polls]'))
    CREATE NONCLUSTERED INDEX [IX_claim_status_polls_history] ON [dbo].[claim_status_polls]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_claim_status_polls_is_active' AND object_id = OBJECT_ID(N'[dbo].[claim_status_polls]'))
    CREATE NONCLUSTERED INDEX [IX_claim_status_polls_is_active] ON [dbo].[claim_status_polls]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_claim_status_polls_created_date' AND object_id = OBJECT_ID(N'[dbo].[claim_status_polls]'))
    CREATE NONCLUSTERED INDEX [IX_claim_status_polls_created_date] ON [dbo].[claim_status_polls]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_claim_status_polls_philhealth_claim_id' AND parent_object_id = OBJECT_ID(N'[dbo].[claim_status_polls]'))
    ALTER TABLE [dbo].[claim_status_polls] ADD CONSTRAINT [FK_claim_status_polls_philhealth_claim_id] FOREIGN KEY ([philhealth_claim_id]) REFERENCES [dbo].[philhealth_claims]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_claim_status_polls_claim_batch_id' AND parent_object_id = OBJECT_ID(N'[dbo].[claim_status_polls]'))
    ALTER TABLE [dbo].[claim_status_polls] ADD CONSTRAINT [FK_claim_status_polls_claim_batch_id] FOREIGN KEY ([claim_batch_id]) REFERENCES [dbo].[claim_batches]([id]) ON DELETE NO ACTION;
-- cf5_shadow_billings
IF OBJECT_ID(N'[dbo].[cf5_shadow_billings]', N'U') IS NOT NULL
    DROP TABLE [dbo].[cf5_shadow_billings];
GO

CREATE TABLE [dbo].[cf5_shadow_billings] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [philhealth_claim_id] BIGINT NOT NULL,
    [drg_code] NVARCHAR(20) NOT NULL,
    [drg_description] NVARCHAR(255) NULL,
    [drg_weight] DECIMAL(10,4) NOT NULL DEFAULT (0),
    [base_rate] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [hf_amount] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [pf_amount] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [total_drg_amount] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [actual_case_rate_amount] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [difference_amount] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [status] NVARCHAR(30) NOT NULL DEFAULT (N'Calculated'),
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_cf5_shadow_billings] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_cf5_shadow_billings_philhealth_claim_id__history' AND object_id = OBJECT_ID(N'[dbo].[cf5_shadow_billings]'))
    ALTER TABLE [dbo].[cf5_shadow_billings] ADD CONSTRAINT [UQ_cf5_shadow_billings_philhealth_claim_id__history] UNIQUE (philhealth_claim_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_cf5_shadow_billings_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[cf5_shadow_billings]'))
    CREATE NONCLUSTERED INDEX [IX_cf5_shadow_billings_tenant_id] ON [dbo].[cf5_shadow_billings]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_cf5_shadow_billings_philhealth_claim_id' AND object_id = OBJECT_ID(N'[dbo].[cf5_shadow_billings]'))
    CREATE NONCLUSTERED INDEX [IX_cf5_shadow_billings_philhealth_claim_id] ON [dbo].[cf5_shadow_billings]([philhealth_claim_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_cf5_shadow_billings_history' AND object_id = OBJECT_ID(N'[dbo].[cf5_shadow_billings]'))
    CREATE NONCLUSTERED INDEX [IX_cf5_shadow_billings_history] ON [dbo].[cf5_shadow_billings]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_cf5_shadow_billings_is_active' AND object_id = OBJECT_ID(N'[dbo].[cf5_shadow_billings]'))
    CREATE NONCLUSTERED INDEX [IX_cf5_shadow_billings_is_active] ON [dbo].[cf5_shadow_billings]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_cf5_shadow_billings_created_date' AND object_id = OBJECT_ID(N'[dbo].[cf5_shadow_billings]'))
    CREATE NONCLUSTERED INDEX [IX_cf5_shadow_billings_created_date] ON [dbo].[cf5_shadow_billings]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_cf5_shadow_billings_status' AND object_id = OBJECT_ID(N'[dbo].[cf5_shadow_billings]'))
    CREATE NONCLUSTERED INDEX [IX_cf5_shadow_billings_status] ON [dbo].[cf5_shadow_billings]([status] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_cf5_shadow_billings_philhealth_claim_id' AND parent_object_id = OBJECT_ID(N'[dbo].[cf5_shadow_billings]'))
    ALTER TABLE [dbo].[cf5_shadow_billings] ADD CONSTRAINT [FK_cf5_shadow_billings_philhealth_claim_id] FOREIGN KEY ([philhealth_claim_id]) REFERENCES [dbo].[philhealth_claims]([id]) ON DELETE NO ACTION;
-- nursing_vitals
IF OBJECT_ID(N'[dbo].[nursing_vitals]', N'U') IS NOT NULL
    DROP TABLE [dbo].[nursing_vitals];
GO

CREATE TABLE [dbo].[nursing_vitals] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [admission_id] BIGINT NOT NULL,
    [recorded_by] BIGINT NULL,
    [recorded_at] DATETIME2 NOT NULL,
    [vital_temperature] DECIMAL(4,1) NULL,
    [vital_heart_rate] INT NULL,
    [vital_respiratory_rate] INT NULL,
    [vital_bp_systolic] INT NULL,
    [vital_bp_diastolic] INT NULL,
    [vital_o2_saturation] DECIMAL(5,2) NULL,
    [vital_pain_score] INT NULL,
    [level_of_consciousness] NVARCHAR(50) NULL,
    [blood_sugar] DECIMAL(5,2) NULL,
    [notes] NVARCHAR(MAX) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    CONSTRAINT [PK_nursing_vitals] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_nursing_vitals_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[nursing_vitals]'))
    CREATE NONCLUSTERED INDEX [IX_nursing_vitals_tenant_id] ON [dbo].[nursing_vitals]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_nursing_vitals_admission_id' AND object_id = OBJECT_ID(N'[dbo].[nursing_vitals]'))
    CREATE NONCLUSTERED INDEX [IX_nursing_vitals_admission_id] ON [dbo].[nursing_vitals]([admission_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_nursing_vitals_history' AND object_id = OBJECT_ID(N'[dbo].[nursing_vitals]'))
    CREATE NONCLUSTERED INDEX [IX_nursing_vitals_history] ON [dbo].[nursing_vitals]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_nursing_vitals_is_active' AND object_id = OBJECT_ID(N'[dbo].[nursing_vitals]'))
    CREATE NONCLUSTERED INDEX [IX_nursing_vitals_is_active] ON [dbo].[nursing_vitals]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_nursing_vitals_created_date' AND object_id = OBJECT_ID(N'[dbo].[nursing_vitals]'))
    CREATE NONCLUSTERED INDEX [IX_nursing_vitals_created_date] ON [dbo].[nursing_vitals]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_nursing_vitals_admission_id' AND parent_object_id = OBJECT_ID(N'[dbo].[nursing_vitals]'))
    ALTER TABLE [dbo].[nursing_vitals] ADD CONSTRAINT [FK_nursing_vitals_admission_id] FOREIGN KEY ([admission_id]) REFERENCES [dbo].[admissions]([id]) ON DELETE CASCADE;
-- nursing_care_notes
IF OBJECT_ID(N'[dbo].[nursing_care_notes]', N'U') IS NOT NULL
    DROP TABLE [dbo].[nursing_care_notes];
GO

CREATE TABLE [dbo].[nursing_care_notes] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [admission_id] BIGINT NOT NULL,
    [nurse_id] BIGINT NULL,
    [care_date] DATE NOT NULL,
    [shift] NVARCHAR(20) NOT NULL DEFAULT (N'AM'),
    [subjective] NVARCHAR(MAX) NULL,
    [objective] NVARCHAR(MAX) NULL,
    [assessment] NVARCHAR(MAX) NULL,
    [plan] NVARCHAR(MAX) NULL,
    [notes] NVARCHAR(MAX) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_nursing_care_notes] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_nursing_care_notes_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[nursing_care_notes]'))
    CREATE NONCLUSTERED INDEX [IX_nursing_care_notes_tenant_id] ON [dbo].[nursing_care_notes]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_nursing_care_notes_admission_id' AND object_id = OBJECT_ID(N'[dbo].[nursing_care_notes]'))
    CREATE NONCLUSTERED INDEX [IX_nursing_care_notes_admission_id] ON [dbo].[nursing_care_notes]([admission_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_nursing_care_notes_history' AND object_id = OBJECT_ID(N'[dbo].[nursing_care_notes]'))
    CREATE NONCLUSTERED INDEX [IX_nursing_care_notes_history] ON [dbo].[nursing_care_notes]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_nursing_care_notes_is_active' AND object_id = OBJECT_ID(N'[dbo].[nursing_care_notes]'))
    CREATE NONCLUSTERED INDEX [IX_nursing_care_notes_is_active] ON [dbo].[nursing_care_notes]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_nursing_care_notes_created_date' AND object_id = OBJECT_ID(N'[dbo].[nursing_care_notes]'))
    CREATE NONCLUSTERED INDEX [IX_nursing_care_notes_created_date] ON [dbo].[nursing_care_notes]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_nursing_care_notes_admission_id' AND parent_object_id = OBJECT_ID(N'[dbo].[nursing_care_notes]'))
    ALTER TABLE [dbo].[nursing_care_notes] ADD CONSTRAINT [FK_nursing_care_notes_admission_id] FOREIGN KEY ([admission_id]) REFERENCES [dbo].[admissions]([id]) ON DELETE CASCADE;
-- nursing_medication_administrations
IF OBJECT_ID(N'[dbo].[nursing_medication_administrations]', N'U') IS NOT NULL
    DROP TABLE [dbo].[nursing_medication_administrations];
GO

CREATE TABLE [dbo].[nursing_medication_administrations] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [admission_id] BIGINT NOT NULL,
    [pharmacy_request_item_id] BIGINT NULL,
    [drug_name] NVARCHAR(200) NOT NULL,
    [dosage] NVARCHAR(100) NULL,
    [dose_given] NVARCHAR(100) NULL,
    [route] NVARCHAR(50) NULL,
    [administered_at] DATETIME2 NOT NULL,
    [administered_by] BIGINT NULL,
    [notes] NVARCHAR(MAX) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    CONSTRAINT [PK_nursing_medication_administrations] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_nursing_medication_administrations_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[nursing_medication_administrations]'))
    CREATE NONCLUSTERED INDEX [IX_nursing_medication_administrations_tenant_id] ON [dbo].[nursing_medication_administrations]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_nursing_medication_administrations_admission_id' AND object_id = OBJECT_ID(N'[dbo].[nursing_medication_administrations]'))
    CREATE NONCLUSTERED INDEX [IX_nursing_medication_administrations_admission_id] ON [dbo].[nursing_medication_administrations]([admission_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_nursing_medication_administrations_pharmacy_request_item_id' AND object_id = OBJECT_ID(N'[dbo].[nursing_medication_administrations]'))
    CREATE NONCLUSTERED INDEX [IX_nursing_medication_administrations_pharmacy_request_item_id] ON [dbo].[nursing_medication_administrations]([pharmacy_request_item_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_nursing_medication_administrations_administered_by' AND object_id = OBJECT_ID(N'[dbo].[nursing_medication_administrations]'))
    CREATE NONCLUSTERED INDEX [IX_nursing_medication_administrations_administered_by] ON [dbo].[nursing_medication_administrations]([administered_by] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_nursing_medication_administrations_history' AND object_id = OBJECT_ID(N'[dbo].[nursing_medication_administrations]'))
    CREATE NONCLUSTERED INDEX [IX_nursing_medication_administrations_history] ON [dbo].[nursing_medication_administrations]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_nursing_medication_administrations_is_active' AND object_id = OBJECT_ID(N'[dbo].[nursing_medication_administrations]'))
    CREATE NONCLUSTERED INDEX [IX_nursing_medication_administrations_is_active] ON [dbo].[nursing_medication_administrations]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_nursing_medication_administrations_created_date' AND object_id = OBJECT_ID(N'[dbo].[nursing_medication_administrations]'))
    CREATE NONCLUSTERED INDEX [IX_nursing_medication_administrations_created_date] ON [dbo].[nursing_medication_administrations]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_nursing_medication_administrations_admission_id' AND parent_object_id = OBJECT_ID(N'[dbo].[nursing_medication_administrations]'))
    ALTER TABLE [dbo].[nursing_medication_administrations] ADD CONSTRAINT [FK_nursing_medication_administrations_admission_id] FOREIGN KEY ([admission_id]) REFERENCES [dbo].[admissions]([id]) ON DELETE CASCADE;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_nursing_medication_administrations_pharmacy_request_item_id' AND parent_object_id = OBJECT_ID(N'[dbo].[nursing_medication_administrations]'))
    ALTER TABLE [dbo].[nursing_medication_administrations] ADD CONSTRAINT [FK_nursing_medication_administrations_pharmacy_request_item_id] FOREIGN KEY ([pharmacy_request_item_id]) REFERENCES [dbo].[pharmacy_request_items]([id]) ON DELETE SET NULL;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_nursing_medication_administrations_administered_by' AND parent_object_id = OBJECT_ID(N'[dbo].[nursing_medication_administrations]'))
    ALTER TABLE [dbo].[nursing_medication_administrations] ADD CONSTRAINT [FK_nursing_medication_administrations_administered_by] FOREIGN KEY ([administered_by]) REFERENCES [dbo].[users]([id]) ON DELETE SET NULL;
-- admission_room_histories
IF OBJECT_ID(N'[dbo].[admission_room_histories]', N'U') IS NOT NULL
    DROP TABLE [dbo].[admission_room_histories];
GO

CREATE TABLE [dbo].[admission_room_histories] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [admission_id] BIGINT NOT NULL,
    [room_type] NVARCHAR(20) NOT NULL,
    [room_id] BIGINT NOT NULL,
    [room_name] NVARCHAR(100) NULL,
    [ward_name] NVARCHAR(100) NULL,
    [room_rate] DECIMAL(10,2) NOT NULL DEFAULT (0),
    [assigned_at] DATETIME2 NOT NULL,
    [unassigned_at] DATETIME2 NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_admission_room_histories] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_admission_room_histories_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[admission_room_histories]'))
    CREATE NONCLUSTERED INDEX [IX_admission_room_histories_tenant_id] ON [dbo].[admission_room_histories]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_admission_room_histories_admission_id' AND object_id = OBJECT_ID(N'[dbo].[admission_room_histories]'))
    CREATE NONCLUSTERED INDEX [IX_admission_room_histories_admission_id] ON [dbo].[admission_room_histories]([admission_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_admission_room_histories_is_active' AND object_id = OBJECT_ID(N'[dbo].[admission_room_histories]'))
    CREATE NONCLUSTERED INDEX [IX_admission_room_histories_is_active] ON [dbo].[admission_room_histories]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_admission_room_histories_created_date' AND object_id = OBJECT_ID(N'[dbo].[admission_room_histories]'))
    CREATE NONCLUSTERED INDEX [IX_admission_room_histories_created_date] ON [dbo].[admission_room_histories]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_admission_room_histories_admission_id' AND parent_object_id = OBJECT_ID(N'[dbo].[admission_room_histories]'))
    ALTER TABLE [dbo].[admission_room_histories] ADD CONSTRAINT [FK_admission_room_histories_admission_id] FOREIGN KEY ([admission_id]) REFERENCES [dbo].[admissions]([id]) ON DELETE NO ACTION;
-- hospital_transfers
IF OBJECT_ID(N'[dbo].[hospital_transfers]', N'U') IS NOT NULL
    DROP TABLE [dbo].[hospital_transfers];
GO

CREATE TABLE [dbo].[hospital_transfers] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [transfer_code] NVARCHAR(20) NOT NULL,
    [patient_id] BIGINT NOT NULL,
    [destination_hospital_id] BIGINT NOT NULL,
    [source_type] NVARCHAR(50) NOT NULL,
    [source_id] BIGINT NOT NULL,
    [transfer_reason] NVARCHAR(MAX) NULL,
    [transfer_diagnosis] NVARCHAR(MAX) NULL,
    [transferred_by] BIGINT NULL,
    [accompanying_personnel] NVARCHAR(255) NULL,
    [transport_mode] NVARCHAR(30) NOT NULL DEFAULT (N'Ambulance'),
    [condition_on_transfer] NVARCHAR(MAX) NULL,
    [status] NVARCHAR(30) NOT NULL DEFAULT (N'Pending'),
    [transfer_datetime] DATETIME2 NULL,
    [completed_datetime] DATETIME2 NULL,
    [notes] NVARCHAR(MAX) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_hospital_transfers] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_hospital_transfers_transfer_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[hospital_transfers]'))
    ALTER TABLE [dbo].[hospital_transfers] ADD CONSTRAINT [UQ_hospital_transfers_transfer_code__tenant_id__history] UNIQUE (transfer_code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_hospital_transfers_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[hospital_transfers]'))
    CREATE NONCLUSTERED INDEX [IX_hospital_transfers_tenant_id] ON [dbo].[hospital_transfers]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_hospital_transfers_patient_id' AND object_id = OBJECT_ID(N'[dbo].[hospital_transfers]'))
    CREATE NONCLUSTERED INDEX [IX_hospital_transfers_patient_id] ON [dbo].[hospital_transfers]([patient_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_hospital_transfers_destination_hospital_id' AND object_id = OBJECT_ID(N'[dbo].[hospital_transfers]'))
    CREATE NONCLUSTERED INDEX [IX_hospital_transfers_destination_hospital_id] ON [dbo].[hospital_transfers]([destination_hospital_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_hospital_transfers_transferred_by' AND object_id = OBJECT_ID(N'[dbo].[hospital_transfers]'))
    CREATE NONCLUSTERED INDEX [IX_hospital_transfers_transferred_by] ON [dbo].[hospital_transfers]([transferred_by] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_hospital_transfers_history' AND object_id = OBJECT_ID(N'[dbo].[hospital_transfers]'))
    CREATE NONCLUSTERED INDEX [IX_hospital_transfers_history] ON [dbo].[hospital_transfers]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_hospital_transfers_is_active' AND object_id = OBJECT_ID(N'[dbo].[hospital_transfers]'))
    CREATE NONCLUSTERED INDEX [IX_hospital_transfers_is_active] ON [dbo].[hospital_transfers]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_hospital_transfers_created_date' AND object_id = OBJECT_ID(N'[dbo].[hospital_transfers]'))
    CREATE NONCLUSTERED INDEX [IX_hospital_transfers_created_date] ON [dbo].[hospital_transfers]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_hospital_transfers_status' AND object_id = OBJECT_ID(N'[dbo].[hospital_transfers]'))
    CREATE NONCLUSTERED INDEX [IX_hospital_transfers_status] ON [dbo].[hospital_transfers]([status] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_hospital_transfers_patient_id' AND parent_object_id = OBJECT_ID(N'[dbo].[hospital_transfers]'))
    ALTER TABLE [dbo].[hospital_transfers] ADD CONSTRAINT [FK_hospital_transfers_patient_id] FOREIGN KEY ([patient_id]) REFERENCES [dbo].[patients]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_hospital_transfers_destination_hospital_id' AND parent_object_id = OBJECT_ID(N'[dbo].[hospital_transfers]'))
    ALTER TABLE [dbo].[hospital_transfers] ADD CONSTRAINT [FK_hospital_transfers_destination_hospital_id] FOREIGN KEY ([destination_hospital_id]) REFERENCES [dbo].[hospitals]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_hospital_transfers_transferred_by' AND parent_object_id = OBJECT_ID(N'[dbo].[hospital_transfers]'))
    ALTER TABLE [dbo].[hospital_transfers] ADD CONSTRAINT [FK_hospital_transfers_transferred_by] FOREIGN KEY ([transferred_by]) REFERENCES [dbo].[health_care_professionals]([id]) ON DELETE NO ACTION;
-- yakap_empanelments
IF OBJECT_ID(N'[dbo].[yakap_empanelments]', N'U') IS NOT NULL
    DROP TABLE [dbo].[yakap_empanelments];
GO

CREATE TABLE [dbo].[yakap_empanelments] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [empanelment_code] NVARCHAR(30) NOT NULL,
    [patient_id] BIGINT NOT NULL,
    [philhealth_member_id] BIGINT NULL,
    [empanelment_year] INT NOT NULL,
    [empanelment_date] DATE NULL,
    [yes_signed_date] DATE NULL,
    [yes_reference_no] NVARCHAR(50) NULL,
    [primary_care_provider_id] BIGINT NULL,
    [opd_clinic_id] BIGINT NULL,
    [risk_classification] NVARCHAR(20) NULL,
    [fpe_completed] TINYINT NOT NULL DEFAULT (0),
    [first_consultation_id] BIGINT NULL,
    [status] NVARCHAR(30) NOT NULL DEFAULT (N'Active'),
    [notes] NVARCHAR(MAX) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_yakap_empanelments] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_yakap_empanelments_empanelment_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[yakap_empanelments]'))
    ALTER TABLE [dbo].[yakap_empanelments] ADD CONSTRAINT [UQ_yakap_empanelments_empanelment_code__tenant_id__history] UNIQUE (empanelment_code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_yakap_empanelments_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[yakap_empanelments]'))
    CREATE NONCLUSTERED INDEX [IX_yakap_empanelments_tenant_id] ON [dbo].[yakap_empanelments]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_yakap_empanelments_patient_id' AND object_id = OBJECT_ID(N'[dbo].[yakap_empanelments]'))
    CREATE NONCLUSTERED INDEX [IX_yakap_empanelments_patient_id] ON [dbo].[yakap_empanelments]([patient_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_yakap_empanelments_philhealth_member_id' AND object_id = OBJECT_ID(N'[dbo].[yakap_empanelments]'))
    CREATE NONCLUSTERED INDEX [IX_yakap_empanelments_philhealth_member_id] ON [dbo].[yakap_empanelments]([philhealth_member_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_yakap_empanelments_primary_care_provider_id' AND object_id = OBJECT_ID(N'[dbo].[yakap_empanelments]'))
    CREATE NONCLUSTERED INDEX [IX_yakap_empanelments_primary_care_provider_id] ON [dbo].[yakap_empanelments]([primary_care_provider_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_yakap_empanelments_opd_clinic_id' AND object_id = OBJECT_ID(N'[dbo].[yakap_empanelments]'))
    CREATE NONCLUSTERED INDEX [IX_yakap_empanelments_opd_clinic_id] ON [dbo].[yakap_empanelments]([opd_clinic_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_yakap_empanelments_first_consultation_id' AND object_id = OBJECT_ID(N'[dbo].[yakap_empanelments]'))
    CREATE NONCLUSTERED INDEX [IX_yakap_empanelments_first_consultation_id] ON [dbo].[yakap_empanelments]([first_consultation_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_yakap_empanelments_history' AND object_id = OBJECT_ID(N'[dbo].[yakap_empanelments]'))
    CREATE NONCLUSTERED INDEX [IX_yakap_empanelments_history] ON [dbo].[yakap_empanelments]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_yakap_empanelments_is_active' AND object_id = OBJECT_ID(N'[dbo].[yakap_empanelments]'))
    CREATE NONCLUSTERED INDEX [IX_yakap_empanelments_is_active] ON [dbo].[yakap_empanelments]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_yakap_empanelments_created_date' AND object_id = OBJECT_ID(N'[dbo].[yakap_empanelments]'))
    CREATE NONCLUSTERED INDEX [IX_yakap_empanelments_created_date] ON [dbo].[yakap_empanelments]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_yakap_empanelments_status' AND object_id = OBJECT_ID(N'[dbo].[yakap_empanelments]'))
    CREATE NONCLUSTERED INDEX [IX_yakap_empanelments_status] ON [dbo].[yakap_empanelments]([status] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_yakap_empanelments_patient_id' AND parent_object_id = OBJECT_ID(N'[dbo].[yakap_empanelments]'))
    ALTER TABLE [dbo].[yakap_empanelments] ADD CONSTRAINT [FK_yakap_empanelments_patient_id] FOREIGN KEY ([patient_id]) REFERENCES [dbo].[patients]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_yakap_empanelments_philhealth_member_id' AND parent_object_id = OBJECT_ID(N'[dbo].[yakap_empanelments]'))
    ALTER TABLE [dbo].[yakap_empanelments] ADD CONSTRAINT [FK_yakap_empanelments_philhealth_member_id] FOREIGN KEY ([philhealth_member_id]) REFERENCES [dbo].[philhealth_members]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_yakap_empanelments_primary_care_provider_id' AND parent_object_id = OBJECT_ID(N'[dbo].[yakap_empanelments]'))
    ALTER TABLE [dbo].[yakap_empanelments] ADD CONSTRAINT [FK_yakap_empanelments_primary_care_provider_id] FOREIGN KEY ([primary_care_provider_id]) REFERENCES [dbo].[health_care_professionals]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_yakap_empanelments_opd_clinic_id' AND parent_object_id = OBJECT_ID(N'[dbo].[yakap_empanelments]'))
    ALTER TABLE [dbo].[yakap_empanelments] ADD CONSTRAINT [FK_yakap_empanelments_opd_clinic_id] FOREIGN KEY ([opd_clinic_id]) REFERENCES [dbo].[opd_clinics]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_yakap_empanelments_first_consultation_id' AND parent_object_id = OBJECT_ID(N'[dbo].[yakap_empanelments]'))
    ALTER TABLE [dbo].[yakap_empanelments] ADD CONSTRAINT [FK_yakap_empanelments_first_consultation_id] FOREIGN KEY ([first_consultation_id]) REFERENCES [dbo].[opd_consultations]([id]) ON DELETE NO ACTION;
-- yakap_fpe_records
IF OBJECT_ID(N'[dbo].[yakap_fpe_records]', N'U') IS NOT NULL
    DROP TABLE [dbo].[yakap_fpe_records];
GO

CREATE TABLE [dbo].[yakap_fpe_records] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [empanelment_id] BIGINT NOT NULL,
    [patient_id] BIGINT NOT NULL,
    [consultation_id] BIGINT NULL,
    [physician_id] BIGINT NULL,
    [fpe_datetime] DATETIME2 NULL,
    [height_cm] DECIMAL(8,2) NULL,
    [weight_kg] DECIMAL(8,2) NULL,
    [bmi] DECIMAL(8,2) NULL,
    [waist_circumference_cm] DECIMAL(8,2) NULL,
    [smoking_status] NVARCHAR(30) NULL,
    [alcohol_use] NVARCHAR(30) NULL,
    [diet_type] NVARCHAR(100) NULL,
    [physical_activity_level] NVARCHAR(30) NULL,
    [family_history] NVARCHAR(MAX) NULL,
    [past_medical_history] NVARCHAR(MAX) NULL,
    [immunization_history] NVARCHAR(MAX) NULL,
    [obstetric_history] NVARCHAR(MAX) NULL,
    [risk_classification] NVARCHAR(20) NULL,
    [risk_score] DECIMAL(8,2) NULL,
    [status] NVARCHAR(30) NOT NULL DEFAULT (N'Pending'),
    [notes] NVARCHAR(MAX) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_yakap_fpe_records] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_yakap_fpe_records_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[yakap_fpe_records]'))
    CREATE NONCLUSTERED INDEX [IX_yakap_fpe_records_tenant_id] ON [dbo].[yakap_fpe_records]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_yakap_fpe_records_empanelment_id' AND object_id = OBJECT_ID(N'[dbo].[yakap_fpe_records]'))
    CREATE NONCLUSTERED INDEX [IX_yakap_fpe_records_empanelment_id] ON [dbo].[yakap_fpe_records]([empanelment_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_yakap_fpe_records_patient_id' AND object_id = OBJECT_ID(N'[dbo].[yakap_fpe_records]'))
    CREATE NONCLUSTERED INDEX [IX_yakap_fpe_records_patient_id] ON [dbo].[yakap_fpe_records]([patient_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_yakap_fpe_records_consultation_id' AND object_id = OBJECT_ID(N'[dbo].[yakap_fpe_records]'))
    CREATE NONCLUSTERED INDEX [IX_yakap_fpe_records_consultation_id] ON [dbo].[yakap_fpe_records]([consultation_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_yakap_fpe_records_physician_id' AND object_id = OBJECT_ID(N'[dbo].[yakap_fpe_records]'))
    CREATE NONCLUSTERED INDEX [IX_yakap_fpe_records_physician_id] ON [dbo].[yakap_fpe_records]([physician_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_yakap_fpe_records_history' AND object_id = OBJECT_ID(N'[dbo].[yakap_fpe_records]'))
    CREATE NONCLUSTERED INDEX [IX_yakap_fpe_records_history] ON [dbo].[yakap_fpe_records]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_yakap_fpe_records_is_active' AND object_id = OBJECT_ID(N'[dbo].[yakap_fpe_records]'))
    CREATE NONCLUSTERED INDEX [IX_yakap_fpe_records_is_active] ON [dbo].[yakap_fpe_records]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_yakap_fpe_records_created_date' AND object_id = OBJECT_ID(N'[dbo].[yakap_fpe_records]'))
    CREATE NONCLUSTERED INDEX [IX_yakap_fpe_records_created_date] ON [dbo].[yakap_fpe_records]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_yakap_fpe_records_status' AND object_id = OBJECT_ID(N'[dbo].[yakap_fpe_records]'))
    CREATE NONCLUSTERED INDEX [IX_yakap_fpe_records_status] ON [dbo].[yakap_fpe_records]([status] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_yakap_fpe_records_empanelment_id' AND parent_object_id = OBJECT_ID(N'[dbo].[yakap_fpe_records]'))
    ALTER TABLE [dbo].[yakap_fpe_records] ADD CONSTRAINT [FK_yakap_fpe_records_empanelment_id] FOREIGN KEY ([empanelment_id]) REFERENCES [dbo].[yakap_empanelments]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_yakap_fpe_records_patient_id' AND parent_object_id = OBJECT_ID(N'[dbo].[yakap_fpe_records]'))
    ALTER TABLE [dbo].[yakap_fpe_records] ADD CONSTRAINT [FK_yakap_fpe_records_patient_id] FOREIGN KEY ([patient_id]) REFERENCES [dbo].[patients]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_yakap_fpe_records_consultation_id' AND parent_object_id = OBJECT_ID(N'[dbo].[yakap_fpe_records]'))
    ALTER TABLE [dbo].[yakap_fpe_records] ADD CONSTRAINT [FK_yakap_fpe_records_consultation_id] FOREIGN KEY ([consultation_id]) REFERENCES [dbo].[opd_consultations]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_yakap_fpe_records_physician_id' AND parent_object_id = OBJECT_ID(N'[dbo].[yakap_fpe_records]'))
    ALTER TABLE [dbo].[yakap_fpe_records] ADD CONSTRAINT [FK_yakap_fpe_records_physician_id] FOREIGN KEY ([physician_id]) REFERENCES [dbo].[health_care_professionals]([id]) ON DELETE NO ACTION;
-- yakap_screenings
IF OBJECT_ID(N'[dbo].[yakap_screenings]', N'U') IS NOT NULL
    DROP TABLE [dbo].[yakap_screenings];
GO

CREATE TABLE [dbo].[yakap_screenings] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [empanelment_id] BIGINT NOT NULL,
    [patient_id] BIGINT NOT NULL,
    [screening_type] NVARCHAR(50) NOT NULL,
    [screening_name] NVARCHAR(200) NOT NULL,
    [screening_date] DATE NULL,
    [result] NVARCHAR(30) NULL,
    [result_details] NVARCHAR(MAX) NULL,
    [facility_name] NVARCHAR(200) NULL,
    [performed_by] BIGINT NULL,
    [status] NVARCHAR(30) NOT NULL DEFAULT (N'Pending'),
    [next_screening_due] DATE NULL,
    [notes] NVARCHAR(MAX) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_yakap_screenings] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_yakap_screenings_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[yakap_screenings]'))
    CREATE NONCLUSTERED INDEX [IX_yakap_screenings_tenant_id] ON [dbo].[yakap_screenings]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_yakap_screenings_empanelment_id' AND object_id = OBJECT_ID(N'[dbo].[yakap_screenings]'))
    CREATE NONCLUSTERED INDEX [IX_yakap_screenings_empanelment_id] ON [dbo].[yakap_screenings]([empanelment_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_yakap_screenings_patient_id' AND object_id = OBJECT_ID(N'[dbo].[yakap_screenings]'))
    CREATE NONCLUSTERED INDEX [IX_yakap_screenings_patient_id] ON [dbo].[yakap_screenings]([patient_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_yakap_screenings_performed_by' AND object_id = OBJECT_ID(N'[dbo].[yakap_screenings]'))
    CREATE NONCLUSTERED INDEX [IX_yakap_screenings_performed_by] ON [dbo].[yakap_screenings]([performed_by] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_yakap_screenings_history' AND object_id = OBJECT_ID(N'[dbo].[yakap_screenings]'))
    CREATE NONCLUSTERED INDEX [IX_yakap_screenings_history] ON [dbo].[yakap_screenings]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_yakap_screenings_is_active' AND object_id = OBJECT_ID(N'[dbo].[yakap_screenings]'))
    CREATE NONCLUSTERED INDEX [IX_yakap_screenings_is_active] ON [dbo].[yakap_screenings]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_yakap_screenings_created_date' AND object_id = OBJECT_ID(N'[dbo].[yakap_screenings]'))
    CREATE NONCLUSTERED INDEX [IX_yakap_screenings_created_date] ON [dbo].[yakap_screenings]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_yakap_screenings_status' AND object_id = OBJECT_ID(N'[dbo].[yakap_screenings]'))
    CREATE NONCLUSTERED INDEX [IX_yakap_screenings_status] ON [dbo].[yakap_screenings]([status] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_yakap_screenings_empanelment_id' AND parent_object_id = OBJECT_ID(N'[dbo].[yakap_screenings]'))
    ALTER TABLE [dbo].[yakap_screenings] ADD CONSTRAINT [FK_yakap_screenings_empanelment_id] FOREIGN KEY ([empanelment_id]) REFERENCES [dbo].[yakap_empanelments]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_yakap_screenings_patient_id' AND parent_object_id = OBJECT_ID(N'[dbo].[yakap_screenings]'))
    ALTER TABLE [dbo].[yakap_screenings] ADD CONSTRAINT [FK_yakap_screenings_patient_id] FOREIGN KEY ([patient_id]) REFERENCES [dbo].[patients]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_yakap_screenings_performed_by' AND parent_object_id = OBJECT_ID(N'[dbo].[yakap_screenings]'))
    ALTER TABLE [dbo].[yakap_screenings] ADD CONSTRAINT [FK_yakap_screenings_performed_by] FOREIGN KEY ([performed_by]) REFERENCES [dbo].[health_care_professionals]([id]) ON DELETE NO ACTION;
-- yakap_capitation_payments
IF OBJECT_ID(N'[dbo].[yakap_capitation_payments]', N'U') IS NOT NULL
    DROP TABLE [dbo].[yakap_capitation_payments];
GO

CREATE TABLE [dbo].[yakap_capitation_payments] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [empanelment_id] BIGINT NOT NULL,
    [patient_id] BIGINT NOT NULL,
    [tranche] TINYINT NOT NULL,
    [amount] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [payment_code] NVARCHAR(50) NULL,
    [payment_date] DATE NULL,
    [status] NVARCHAR(30) NOT NULL DEFAULT (N'Pending'),
    [philhealth_reference] NVARCHAR(100) NULL,
    [notes] NVARCHAR(MAX) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NULL,
    [updated_date] DATETIME2 NULL,
    [is_yakap] TINYINT NOT NULL DEFAULT (0),
    [empanelment_id] BIGINT NULL,
    [risk_classification] NVARCHAR(20) NULL,
    [fpe_completed] TINYINT NOT NULL DEFAULT (0),
    [Table] COLUMN NULL,
    [pharmacy_request_items] `IS_GAMOT` NULL DEFAULT (0),
    [lab_request_items] `IS_YAKAP_LAB` NULL DEFAULT (0),
    [opd_clinics] `IS_YAKAP_CLINIC` NULL DEFAULT (0),
    [health_care_professionals] `IS_YAKAP_PHYSICIAN` NULL DEFAULT (0),
    CONSTRAINT [PK_yakap_capitation_payments] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_yakap_capitation_payments_empanelment_id__tranche__history' AND object_id = OBJECT_ID(N'[dbo].[yakap_capitation_payments]'))
    ALTER TABLE [dbo].[yakap_capitation_payments] ADD CONSTRAINT [UQ_yakap_capitation_payments_empanelment_id__tranche__history] UNIQUE (empanelment_id, tranche, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_yakap_capitation_payments_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[yakap_capitation_payments]'))
    CREATE NONCLUSTERED INDEX [IX_yakap_capitation_payments_tenant_id] ON [dbo].[yakap_capitation_payments]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_yakap_capitation_payments_empanelment_id' AND object_id = OBJECT_ID(N'[dbo].[yakap_capitation_payments]'))
    CREATE NONCLUSTERED INDEX [IX_yakap_capitation_payments_empanelment_id] ON [dbo].[yakap_capitation_payments]([empanelment_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_yakap_capitation_payments_patient_id' AND object_id = OBJECT_ID(N'[dbo].[yakap_capitation_payments]'))
    CREATE NONCLUSTERED INDEX [IX_yakap_capitation_payments_patient_id] ON [dbo].[yakap_capitation_payments]([patient_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_yakap_capitation_payments_history' AND object_id = OBJECT_ID(N'[dbo].[yakap_capitation_payments]'))
    CREATE NONCLUSTERED INDEX [IX_yakap_capitation_payments_history] ON [dbo].[yakap_capitation_payments]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_yakap_capitation_payments_is_active' AND object_id = OBJECT_ID(N'[dbo].[yakap_capitation_payments]'))
    CREATE NONCLUSTERED INDEX [IX_yakap_capitation_payments_is_active] ON [dbo].[yakap_capitation_payments]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_yakap_capitation_payments_created_date' AND object_id = OBJECT_ID(N'[dbo].[yakap_capitation_payments]'))
    CREATE NONCLUSTERED INDEX [IX_yakap_capitation_payments_created_date] ON [dbo].[yakap_capitation_payments]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_yakap_capitation_payments_status' AND object_id = OBJECT_ID(N'[dbo].[yakap_capitation_payments]'))
    CREATE NONCLUSTERED INDEX [IX_yakap_capitation_payments_status] ON [dbo].[yakap_capitation_payments]([status] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_yakap_capitation_payments_empanelment_id' AND parent_object_id = OBJECT_ID(N'[dbo].[yakap_capitation_payments]'))
    ALTER TABLE [dbo].[yakap_capitation_payments] ADD CONSTRAINT [FK_yakap_capitation_payments_empanelment_id] FOREIGN KEY ([empanelment_id]) REFERENCES [dbo].[yakap_empanelments]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_yakap_capitation_payments_patient_id' AND parent_object_id = OBJECT_ID(N'[dbo].[yakap_capitation_payments]'))
    ALTER TABLE [dbo].[yakap_capitation_payments] ADD CONSTRAINT [FK_yakap_capitation_payments_patient_id] FOREIGN KEY ([patient_id]) REFERENCES [dbo].[patients]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_yakap_capitation_payments_empanelment_id' AND parent_object_id = OBJECT_ID(N'[dbo].[yakap_capitation_payments]'))
    ALTER TABLE [dbo].[yakap_capitation_payments] ADD CONSTRAINT [FK_yakap_capitation_payments_empanelment_id] FOREIGN KEY ([empanelment_id]) REFERENCES [dbo].[yakap_empanelments]([id]) ON DELETE NO ACTION;
-- konsulta_enrollments
IF OBJECT_ID(N'[dbo].[konsulta_enrollments]', N'U') IS NOT NULL
    DROP TABLE [dbo].[konsulta_enrollments];
GO

CREATE TABLE [dbo].[konsulta_enrollments] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [enrollment_code] NVARCHAR(30) NOT NULL,
    [patient_id] BIGINT NOT NULL,
    [philhealth_member_id] BIGINT NULL,
    [primary_care_provider_id] BIGINT NULL,
    [enrollment_date] DATE NOT NULL,
    [expiry_date] DATE NULL,
    [status] NVARCHAR(30) NOT NULL DEFAULT (N'Active'),
    [risk_classification] NVARCHAR(100) NULL,
    [fpe_completed] TINYINT NOT NULL DEFAULT (0),
    [fpe_completed_at] DATETIME2 NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_konsulta_enrollments] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_konsulta_enrollments_enrollment_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[konsulta_enrollments]'))
    ALTER TABLE [dbo].[konsulta_enrollments] ADD CONSTRAINT [UQ_konsulta_enrollments_enrollment_code__tenant_id__history] UNIQUE (enrollment_code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_konsulta_enrollments_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[konsulta_enrollments]'))
    CREATE NONCLUSTERED INDEX [IX_konsulta_enrollments_tenant_id] ON [dbo].[konsulta_enrollments]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_konsulta_enrollments_patient_id' AND object_id = OBJECT_ID(N'[dbo].[konsulta_enrollments]'))
    CREATE NONCLUSTERED INDEX [IX_konsulta_enrollments_patient_id] ON [dbo].[konsulta_enrollments]([patient_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_konsulta_enrollments_philhealth_member_id' AND object_id = OBJECT_ID(N'[dbo].[konsulta_enrollments]'))
    CREATE NONCLUSTERED INDEX [IX_konsulta_enrollments_philhealth_member_id] ON [dbo].[konsulta_enrollments]([philhealth_member_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_konsulta_enrollments_primary_care_provider_id' AND object_id = OBJECT_ID(N'[dbo].[konsulta_enrollments]'))
    CREATE NONCLUSTERED INDEX [IX_konsulta_enrollments_primary_care_provider_id] ON [dbo].[konsulta_enrollments]([primary_care_provider_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_konsulta_enrollments_history' AND object_id = OBJECT_ID(N'[dbo].[konsulta_enrollments]'))
    CREATE NONCLUSTERED INDEX [IX_konsulta_enrollments_history] ON [dbo].[konsulta_enrollments]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_konsulta_enrollments_is_active' AND object_id = OBJECT_ID(N'[dbo].[konsulta_enrollments]'))
    CREATE NONCLUSTERED INDEX [IX_konsulta_enrollments_is_active] ON [dbo].[konsulta_enrollments]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_konsulta_enrollments_created_date' AND object_id = OBJECT_ID(N'[dbo].[konsulta_enrollments]'))
    CREATE NONCLUSTERED INDEX [IX_konsulta_enrollments_created_date] ON [dbo].[konsulta_enrollments]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_konsulta_enrollments_status' AND object_id = OBJECT_ID(N'[dbo].[konsulta_enrollments]'))
    CREATE NONCLUSTERED INDEX [IX_konsulta_enrollments_status] ON [dbo].[konsulta_enrollments]([status] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_konsulta_enrollments_patient_id' AND parent_object_id = OBJECT_ID(N'[dbo].[konsulta_enrollments]'))
    ALTER TABLE [dbo].[konsulta_enrollments] ADD CONSTRAINT [FK_konsulta_enrollments_patient_id] FOREIGN KEY ([patient_id]) REFERENCES [dbo].[patients]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_konsulta_enrollments_philhealth_member_id' AND parent_object_id = OBJECT_ID(N'[dbo].[konsulta_enrollments]'))
    ALTER TABLE [dbo].[konsulta_enrollments] ADD CONSTRAINT [FK_konsulta_enrollments_philhealth_member_id] FOREIGN KEY ([philhealth_member_id]) REFERENCES [dbo].[philhealth_members]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_konsulta_enrollments_primary_care_provider_id' AND parent_object_id = OBJECT_ID(N'[dbo].[konsulta_enrollments]'))
    ALTER TABLE [dbo].[konsulta_enrollments] ADD CONSTRAINT [FK_konsulta_enrollments_primary_care_provider_id] FOREIGN KEY ([primary_care_provider_id]) REFERENCES [dbo].[health_care_professionals]([id]) ON DELETE NO ACTION;
-- konsulta_consultations
IF OBJECT_ID(N'[dbo].[konsulta_consultations]', N'U') IS NOT NULL
    DROP TABLE [dbo].[konsulta_consultations];
GO

CREATE TABLE [dbo].[konsulta_consultations] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [consultation_code] NVARCHAR(30) NOT NULL,
    [enrollment_id] BIGINT NULL,
    [patient_id] BIGINT NOT NULL,
    [provider_id] BIGINT NULL,
    [consultation_date] DATETIME2 NOT NULL,
    [chief_complaint] NVARCHAR(MAX) NULL,
    [diagnosis] NVARCHAR(MAX) NULL,
    [icd10_code_id] BIGINT NULL,
    [assessment] NVARCHAR(MAX) NULL,
    [management_plan] NVARCHAR(MAX) NULL,
    [notes] NVARCHAR(MAX) NULL,
    [bp] NVARCHAR(20) NULL,
    [temperature] DECIMAL(5,2) NULL,
    [heart_rate] INT NULL,
    [respiratory_rate] INT NULL,
    [weight] DECIMAL(6,2) NULL,
    [height] DECIMAL(6,2) NULL,
    [is_fpe] TINYINT NOT NULL DEFAULT (0),
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_konsulta_consultations] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_konsulta_consultations_consultation_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[konsulta_consultations]'))
    ALTER TABLE [dbo].[konsulta_consultations] ADD CONSTRAINT [UQ_konsulta_consultations_consultation_code__tenant_id__history] UNIQUE (consultation_code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_konsulta_consultations_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[konsulta_consultations]'))
    CREATE NONCLUSTERED INDEX [IX_konsulta_consultations_tenant_id] ON [dbo].[konsulta_consultations]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_konsulta_consultations_enrollment_id' AND object_id = OBJECT_ID(N'[dbo].[konsulta_consultations]'))
    CREATE NONCLUSTERED INDEX [IX_konsulta_consultations_enrollment_id] ON [dbo].[konsulta_consultations]([enrollment_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_konsulta_consultations_patient_id' AND object_id = OBJECT_ID(N'[dbo].[konsulta_consultations]'))
    CREATE NONCLUSTERED INDEX [IX_konsulta_consultations_patient_id] ON [dbo].[konsulta_consultations]([patient_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_konsulta_consultations_provider_id' AND object_id = OBJECT_ID(N'[dbo].[konsulta_consultations]'))
    CREATE NONCLUSTERED INDEX [IX_konsulta_consultations_provider_id] ON [dbo].[konsulta_consultations]([provider_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_konsulta_consultations_icd10_code_id' AND object_id = OBJECT_ID(N'[dbo].[konsulta_consultations]'))
    CREATE NONCLUSTERED INDEX [IX_konsulta_consultations_icd10_code_id] ON [dbo].[konsulta_consultations]([icd10_code_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_konsulta_consultations_history' AND object_id = OBJECT_ID(N'[dbo].[konsulta_consultations]'))
    CREATE NONCLUSTERED INDEX [IX_konsulta_consultations_history] ON [dbo].[konsulta_consultations]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_konsulta_consultations_is_active' AND object_id = OBJECT_ID(N'[dbo].[konsulta_consultations]'))
    CREATE NONCLUSTERED INDEX [IX_konsulta_consultations_is_active] ON [dbo].[konsulta_consultations]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_konsulta_consultations_created_date' AND object_id = OBJECT_ID(N'[dbo].[konsulta_consultations]'))
    CREATE NONCLUSTERED INDEX [IX_konsulta_consultations_created_date] ON [dbo].[konsulta_consultations]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_konsulta_consultations_enrollment_id' AND parent_object_id = OBJECT_ID(N'[dbo].[konsulta_consultations]'))
    ALTER TABLE [dbo].[konsulta_consultations] ADD CONSTRAINT [FK_konsulta_consultations_enrollment_id] FOREIGN KEY ([enrollment_id]) REFERENCES [dbo].[konsulta_enrollments]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_konsulta_consultations_patient_id' AND parent_object_id = OBJECT_ID(N'[dbo].[konsulta_consultations]'))
    ALTER TABLE [dbo].[konsulta_consultations] ADD CONSTRAINT [FK_konsulta_consultations_patient_id] FOREIGN KEY ([patient_id]) REFERENCES [dbo].[patients]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_konsulta_consultations_provider_id' AND parent_object_id = OBJECT_ID(N'[dbo].[konsulta_consultations]'))
    ALTER TABLE [dbo].[konsulta_consultations] ADD CONSTRAINT [FK_konsulta_consultations_provider_id] FOREIGN KEY ([provider_id]) REFERENCES [dbo].[health_care_professionals]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_konsulta_consultations_icd10_code_id' AND parent_object_id = OBJECT_ID(N'[dbo].[konsulta_consultations]'))
    ALTER TABLE [dbo].[konsulta_consultations] ADD CONSTRAINT [FK_konsulta_consultations_icd10_code_id] FOREIGN KEY ([icd10_code_id]) REFERENCES [dbo].[icd_10_codes]([id]) ON DELETE NO ACTION;
-- konsulta_prescriptions
IF OBJECT_ID(N'[dbo].[konsulta_prescriptions]', N'U') IS NOT NULL
    DROP TABLE [dbo].[konsulta_prescriptions];
GO

CREATE TABLE [dbo].[konsulta_prescriptions] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [consultation_id] BIGINT NOT NULL,
    [enrollment_id] BIGINT NULL,
    [patient_id] BIGINT NOT NULL,
    [medicine_name] NVARCHAR(200) NOT NULL,
    [dosage] NVARCHAR(100) NULL,
    [frequency] NVARCHAR(100) NULL,
    [duration] NVARCHAR(100) NULL,
    [quantity] INT NOT NULL DEFAULT (1),
    [is_gamot_covered] TINYINT NOT NULL DEFAULT (0),
    [status] NVARCHAR(30) NOT NULL DEFAULT (N'Prescribed'),
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_konsulta_prescriptions] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_konsulta_prescriptions_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[konsulta_prescriptions]'))
    CREATE NONCLUSTERED INDEX [IX_konsulta_prescriptions_tenant_id] ON [dbo].[konsulta_prescriptions]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_konsulta_prescriptions_consultation_id' AND object_id = OBJECT_ID(N'[dbo].[konsulta_prescriptions]'))
    CREATE NONCLUSTERED INDEX [IX_konsulta_prescriptions_consultation_id] ON [dbo].[konsulta_prescriptions]([consultation_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_konsulta_prescriptions_enrollment_id' AND object_id = OBJECT_ID(N'[dbo].[konsulta_prescriptions]'))
    CREATE NONCLUSTERED INDEX [IX_konsulta_prescriptions_enrollment_id] ON [dbo].[konsulta_prescriptions]([enrollment_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_konsulta_prescriptions_patient_id' AND object_id = OBJECT_ID(N'[dbo].[konsulta_prescriptions]'))
    CREATE NONCLUSTERED INDEX [IX_konsulta_prescriptions_patient_id] ON [dbo].[konsulta_prescriptions]([patient_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_konsulta_prescriptions_history' AND object_id = OBJECT_ID(N'[dbo].[konsulta_prescriptions]'))
    CREATE NONCLUSTERED INDEX [IX_konsulta_prescriptions_history] ON [dbo].[konsulta_prescriptions]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_konsulta_prescriptions_is_active' AND object_id = OBJECT_ID(N'[dbo].[konsulta_prescriptions]'))
    CREATE NONCLUSTERED INDEX [IX_konsulta_prescriptions_is_active] ON [dbo].[konsulta_prescriptions]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_konsulta_prescriptions_created_date' AND object_id = OBJECT_ID(N'[dbo].[konsulta_prescriptions]'))
    CREATE NONCLUSTERED INDEX [IX_konsulta_prescriptions_created_date] ON [dbo].[konsulta_prescriptions]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_konsulta_prescriptions_status' AND object_id = OBJECT_ID(N'[dbo].[konsulta_prescriptions]'))
    CREATE NONCLUSTERED INDEX [IX_konsulta_prescriptions_status] ON [dbo].[konsulta_prescriptions]([status] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_konsulta_prescriptions_consultation_id' AND parent_object_id = OBJECT_ID(N'[dbo].[konsulta_prescriptions]'))
    ALTER TABLE [dbo].[konsulta_prescriptions] ADD CONSTRAINT [FK_konsulta_prescriptions_consultation_id] FOREIGN KEY ([consultation_id]) REFERENCES [dbo].[konsulta_consultations]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_konsulta_prescriptions_enrollment_id' AND parent_object_id = OBJECT_ID(N'[dbo].[konsulta_prescriptions]'))
    ALTER TABLE [dbo].[konsulta_prescriptions] ADD CONSTRAINT [FK_konsulta_prescriptions_enrollment_id] FOREIGN KEY ([enrollment_id]) REFERENCES [dbo].[konsulta_enrollments]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_konsulta_prescriptions_patient_id' AND parent_object_id = OBJECT_ID(N'[dbo].[konsulta_prescriptions]'))
    ALTER TABLE [dbo].[konsulta_prescriptions] ADD CONSTRAINT [FK_konsulta_prescriptions_patient_id] FOREIGN KEY ([patient_id]) REFERENCES [dbo].[patients]([id]) ON DELETE NO ACTION;
-- konsulta_lab_referrals
IF OBJECT_ID(N'[dbo].[konsulta_lab_referrals]', N'U') IS NOT NULL
    DROP TABLE [dbo].[konsulta_lab_referrals];
GO

CREATE TABLE [dbo].[konsulta_lab_referrals] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [consultation_id] BIGINT NOT NULL,
    [enrollment_id] BIGINT NULL,
    [patient_id] BIGINT NOT NULL,
    [test_type] NVARCHAR(200) NOT NULL,
    [referral_date] DATE NOT NULL,
    [performed_date] DATE NULL,
    [result] NVARCHAR(MAX) NULL,
    [status] NVARCHAR(30) NOT NULL DEFAULT (N'Pending'),
    [notes] NVARCHAR(MAX) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_konsulta_lab_referrals] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_konsulta_lab_referrals_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[konsulta_lab_referrals]'))
    CREATE NONCLUSTERED INDEX [IX_konsulta_lab_referrals_tenant_id] ON [dbo].[konsulta_lab_referrals]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_konsulta_lab_referrals_consultation_id' AND object_id = OBJECT_ID(N'[dbo].[konsulta_lab_referrals]'))
    CREATE NONCLUSTERED INDEX [IX_konsulta_lab_referrals_consultation_id] ON [dbo].[konsulta_lab_referrals]([consultation_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_konsulta_lab_referrals_enrollment_id' AND object_id = OBJECT_ID(N'[dbo].[konsulta_lab_referrals]'))
    CREATE NONCLUSTERED INDEX [IX_konsulta_lab_referrals_enrollment_id] ON [dbo].[konsulta_lab_referrals]([enrollment_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_konsulta_lab_referrals_patient_id' AND object_id = OBJECT_ID(N'[dbo].[konsulta_lab_referrals]'))
    CREATE NONCLUSTERED INDEX [IX_konsulta_lab_referrals_patient_id] ON [dbo].[konsulta_lab_referrals]([patient_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_konsulta_lab_referrals_history' AND object_id = OBJECT_ID(N'[dbo].[konsulta_lab_referrals]'))
    CREATE NONCLUSTERED INDEX [IX_konsulta_lab_referrals_history] ON [dbo].[konsulta_lab_referrals]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_konsulta_lab_referrals_is_active' AND object_id = OBJECT_ID(N'[dbo].[konsulta_lab_referrals]'))
    CREATE NONCLUSTERED INDEX [IX_konsulta_lab_referrals_is_active] ON [dbo].[konsulta_lab_referrals]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_konsulta_lab_referrals_created_date' AND object_id = OBJECT_ID(N'[dbo].[konsulta_lab_referrals]'))
    CREATE NONCLUSTERED INDEX [IX_konsulta_lab_referrals_created_date] ON [dbo].[konsulta_lab_referrals]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_konsulta_lab_referrals_status' AND object_id = OBJECT_ID(N'[dbo].[konsulta_lab_referrals]'))
    CREATE NONCLUSTERED INDEX [IX_konsulta_lab_referrals_status] ON [dbo].[konsulta_lab_referrals]([status] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_konsulta_lab_referrals_consultation_id' AND parent_object_id = OBJECT_ID(N'[dbo].[konsulta_lab_referrals]'))
    ALTER TABLE [dbo].[konsulta_lab_referrals] ADD CONSTRAINT [FK_konsulta_lab_referrals_consultation_id] FOREIGN KEY ([consultation_id]) REFERENCES [dbo].[konsulta_consultations]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_konsulta_lab_referrals_enrollment_id' AND parent_object_id = OBJECT_ID(N'[dbo].[konsulta_lab_referrals]'))
    ALTER TABLE [dbo].[konsulta_lab_referrals] ADD CONSTRAINT [FK_konsulta_lab_referrals_enrollment_id] FOREIGN KEY ([enrollment_id]) REFERENCES [dbo].[konsulta_enrollments]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_konsulta_lab_referrals_patient_id' AND parent_object_id = OBJECT_ID(N'[dbo].[konsulta_lab_referrals]'))
    ALTER TABLE [dbo].[konsulta_lab_referrals] ADD CONSTRAINT [FK_konsulta_lab_referrals_patient_id] FOREIGN KEY ([patient_id]) REFERENCES [dbo].[patients]([id]) ON DELETE NO ACTION;

-- [Extra: icu_wards]
-- icu_wards
IF OBJECT_ID(N'[dbo].[icu_wards]', N'U') IS NOT NULL
    DROP TABLE [dbo].[icu_wards];
GO

CREATE TABLE [dbo].[icu_wards] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [location] NVARCHAR(255) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_icu_wards] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_icu_wards_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[icu_wards]'))
    ALTER TABLE [dbo].[icu_wards] ADD CONSTRAINT [UQ_icu_wards_code__tenant_id__history] UNIQUE (code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_icu_wards_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[icu_wards]'))
    CREATE NONCLUSTERED INDEX [IX_icu_wards_tenant_id] ON [dbo].[icu_wards]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_icu_wards_history' AND object_id = OBJECT_ID(N'[dbo].[icu_wards]'))
    CREATE NONCLUSTERED INDEX [IX_icu_wards_history] ON [dbo].[icu_wards]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_icu_wards_is_active' AND object_id = OBJECT_ID(N'[dbo].[icu_wards]'))
    CREATE NONCLUSTERED INDEX [IX_icu_wards_is_active] ON [dbo].[icu_wards]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_icu_wards_created_date' AND object_id = OBJECT_ID(N'[dbo].[icu_wards]'))
    CREATE NONCLUSTERED INDEX [IX_icu_wards_created_date] ON [dbo].[icu_wards]([created_date] ASC);

-- [Extra: icu_beds]
-- icu_beds
IF OBJECT_ID(N'[dbo].[icu_beds]', N'U') IS NOT NULL
    DROP TABLE [dbo].[icu_beds];
GO

CREATE TABLE [dbo].[icu_beds] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [code] NVARCHAR(20) NOT NULL,
    [name] NVARCHAR(100) NOT NULL,
    [ward_id] BIGINT NULL,
    [status] NVARCHAR(30) NOT NULL DEFAULT (N'Available'),
    [room_rate] DECIMAL(10,2) NULL,
    [is_active] TINYINT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NOT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NOT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_icu_beds] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'UQ_icu_beds_code__tenant_id__history' AND object_id = OBJECT_ID(N'[dbo].[icu_beds]'))
    ALTER TABLE [dbo].[icu_beds] ADD CONSTRAINT [UQ_icu_beds_code__tenant_id__history] UNIQUE (code, tenant_id, history);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_icu_beds_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[icu_beds]'))
    CREATE NONCLUSTERED INDEX [IX_icu_beds_tenant_id] ON [dbo].[icu_beds]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_icu_beds_ward_id' AND object_id = OBJECT_ID(N'[dbo].[icu_beds]'))
    CREATE NONCLUSTERED INDEX [IX_icu_beds_ward_id] ON [dbo].[icu_beds]([ward_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_icu_beds_history' AND object_id = OBJECT_ID(N'[dbo].[icu_beds]'))
    CREATE NONCLUSTERED INDEX [IX_icu_beds_history] ON [dbo].[icu_beds]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_icu_beds_is_active' AND object_id = OBJECT_ID(N'[dbo].[icu_beds]'))
    CREATE NONCLUSTERED INDEX [IX_icu_beds_is_active] ON [dbo].[icu_beds]([is_active] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_icu_beds_created_date' AND object_id = OBJECT_ID(N'[dbo].[icu_beds]'))
    CREATE NONCLUSTERED INDEX [IX_icu_beds_created_date] ON [dbo].[icu_beds]([created_date] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_icu_beds_status' AND object_id = OBJECT_ID(N'[dbo].[icu_beds]'))
    CREATE NONCLUSTERED INDEX [IX_icu_beds_status] ON [dbo].[icu_beds]([status] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_icu_beds_ward_id' AND parent_object_id = OBJECT_ID(N'[dbo].[icu_beds]'))
    ALTER TABLE [dbo].[icu_beds] ADD CONSTRAINT [FK_icu_beds_ward_id] FOREIGN KEY ([ward_id]) REFERENCES [dbo].[icu_wards]([id]) ON DELETE SET NULL;

-- [Extra: philhealth_claims]
-- philhealth_claims
IF OBJECT_ID(N'[dbo].[philhealth_claims]', N'U') IS NOT NULL
    DROP TABLE [dbo].[philhealth_claims];
GO

CREATE TABLE [dbo].[philhealth_claims] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [claim_code] NVARCHAR(30) NOT NULL,
    [patient_id] BIGINT NOT NULL,
    [er_admission_id] BIGINT NULL,
    [admission_id] BIGINT NULL,
    [philhealth_member_id] BIGINT NULL,
    [claim_batch_id] BIGINT NULL,
    [status] NVARCHAR(30) NOT NULL DEFAULT (N'Draft'),
    [claim_type] NVARCHAR(10) NOT NULL DEFAULT (N'ACR'),
    [admitting_diagnosis] NVARCHAR(255) NULL,
    [discharge_diagnosis] NVARCHAR(255) NULL,
    [icd10_code_id] BIGINT NULL,
    [date_admitted] DATE NULL,
    [time_admitted] TIME(7) NULL,
    [date_discharged] DATE NULL,
    [time_discharged] TIME(7) NULL,
    [history_of_present_illness] NVARCHAR(MAX) NULL,
    [pertinent_past_medical_history] NVARCHAR(MAX) NULL,
    [physical_exam_findings] NVARCHAR(MAX) NULL,
    [course_in_ward] NVARCHAR(MAX) NULL,
    [outcome] NVARCHAR(50) NULL,
    [case_rate_id] BIGINT NULL,
    [total_actual_amount] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [total_claimed_amount] DECIMAL(12,2) NOT NULL DEFAULT (0),
    [remarks] NVARCHAR(MAX) NULL,
    [is_active] BIT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NULL,
    [updated_date] DATETIME2 NULL,
    [ecs_tracking_no] NVARCHAR(50) NULL,
    [ecs_uploaded_at] DATETIME2 NULL,
    [cloud_folder] NVARCHAR(255) NULL,
    [cloud_file] NVARCHAR(255) NULL,
    [cloud_uploaded_at] DATETIME2 NULL,
    [acknowledgment_code] NVARCHAR(50) NULL,
    [acknowledgment_date] DATETIME2 NULL,
    [voucher_number] NVARCHAR(50) NULL,
    [voucher_amount] DECIMAL(12,2) NULL,
    [voucher_date] DATETIME2 NULL,
    [relationship_to_member] NVARCHAR(10) NULL,
    [chief_complaint] NVARCHAR(MAX) NULL,
    [review_of_systems] NVARCHAR(MAX) NULL,
    [blood_pressure] NVARCHAR(20) NULL,
    [heart_rate] NVARCHAR(10) NULL,
    [temperature] NVARCHAR(10) NULL,
    [respiratory_rate] NVARCHAR(10) NULL,
    [lmp_date] DATE NULL,
    [edc_date] DATE NULL,
    [gravida] TINYINT NULL,
    [para] TINYINT NULL,
    [attending_physician_id] BIGINT NULL,
    [referring_physician_id] BIGINT NULL,
    [patient_signature_date] DATETIME2 NULL,
    [physician_signature_date] DATETIME2 NULL,
    [secondary_icd10_code_id] BIGINT NULL,
    [tertiary_icd10_code_id] BIGINT NULL,
    [hci_signatory_name] NVARCHAR(200) NULL,
    [hci_signature_date] DATETIME2 NULL,
    [lab_imaging_summary] NVARCHAR(MAX) NULL,
    [delivery_date] DATE NULL,
    [delivery_type_id] BIGINT NULL,
    [newborn_outcome] NVARCHAR(50) NULL,
    [patient_is] NVARCHAR(5) NULL,
    [membership_type] NVARCHAR(10) NULL,
    [encounter_type] NVARCHAR(20) NULL,
    [disposition_code] NVARCHAR(5) NULL,
    [tracking_number] NVARCHAR(50) NULL,
    [pension_type] NVARCHAR(10) NULL,
    [gender] NVARCHAR(5) NULL,
    [second_case_rate_id] BIGINT NULL,
    CONSTRAINT [PK_philhealth_claims] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_philhealth_claims_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[philhealth_claims]'))
    CREATE NONCLUSTERED INDEX [IX_philhealth_claims_tenant_id] ON [dbo].[philhealth_claims]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_philhealth_claims_patient_id' AND object_id = OBJECT_ID(N'[dbo].[philhealth_claims]'))
    CREATE NONCLUSTERED INDEX [IX_philhealth_claims_patient_id] ON [dbo].[philhealth_claims]([patient_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_philhealth_claims_er_admission_id' AND object_id = OBJECT_ID(N'[dbo].[philhealth_claims]'))
    CREATE NONCLUSTERED INDEX [IX_philhealth_claims_er_admission_id] ON [dbo].[philhealth_claims]([er_admission_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_philhealth_claims_admission_id' AND object_id = OBJECT_ID(N'[dbo].[philhealth_claims]'))
    CREATE NONCLUSTERED INDEX [IX_philhealth_claims_admission_id] ON [dbo].[philhealth_claims]([admission_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_philhealth_claims_claim_batch_id' AND object_id = OBJECT_ID(N'[dbo].[philhealth_claims]'))
    CREATE NONCLUSTERED INDEX [IX_philhealth_claims_claim_batch_id] ON [dbo].[philhealth_claims]([claim_batch_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_philhealth_claims_status' AND object_id = OBJECT_ID(N'[dbo].[philhealth_claims]'))
    CREATE NONCLUSTERED INDEX [IX_philhealth_claims_status] ON [dbo].[philhealth_claims]([status] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_philhealth_claims_history' AND object_id = OBJECT_ID(N'[dbo].[philhealth_claims]'))
    CREATE NONCLUSTERED INDEX [IX_philhealth_claims_history] ON [dbo].[philhealth_claims]([history] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_philhealth_claims_er_admission_id' AND parent_object_id = OBJECT_ID(N'[dbo].[philhealth_claims]'))
    ALTER TABLE [dbo].[philhealth_claims] ADD CONSTRAINT [FK_philhealth_claims_er_admission_id] FOREIGN KEY ([er_admission_id]) REFERENCES [dbo].[er_admissions]([id]) ON DELETE NO ACTION;
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_philhealth_claims_admission_id' AND parent_object_id = OBJECT_ID(N'[dbo].[philhealth_claims]'))
    ALTER TABLE [dbo].[philhealth_claims] ADD CONSTRAINT [FK_philhealth_claims_admission_id] FOREIGN KEY ([admission_id]) REFERENCES [dbo].[admissions]([id]) ON DELETE NO ACTION;

-- [Extra: claim_procedures]
-- claim_procedures
IF OBJECT_ID(N'[dbo].[claim_procedures]', N'U') IS NOT NULL
    DROP TABLE [dbo].[claim_procedures];
GO

CREATE TABLE [dbo].[claim_procedures] (
    [id] BIGINT IDENTITY(1,1) NOT NULL,
    [tenant_id] BIGINT NOT NULL,
    [philhealth_claim_id] BIGINT NOT NULL,
    [sequence] INT NOT NULL DEFAULT (1),
    [procedure_description] NVARCHAR(MAX) NULL,
    [rvs_code] NVARCHAR(10) NULL,
    [procedure_date] DATE NULL,
    [laterality] NVARCHAR(20) NULL,
    [special_consideration] NVARCHAR(50) NULL,
    [is_active] BIT NOT NULL DEFAULT (1),
    [history] TINYINT NOT NULL DEFAULT (1),
    [created_by] BIGINT NULL,
    [created_date] DATETIME2 NULL,
    [updated_by] BIGINT NULL,
    [updated_date] DATETIME2 NULL,
    CONSTRAINT [PK_claim_procedures] PRIMARY KEY CLUSTERED ([id] ASC)
);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_claim_procedures_tenant_id' AND object_id = OBJECT_ID(N'[dbo].[claim_procedures]'))
    CREATE NONCLUSTERED INDEX [IX_claim_procedures_tenant_id] ON [dbo].[claim_procedures]([tenant_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_claim_procedures_philhealth_claim_id' AND object_id = OBJECT_ID(N'[dbo].[claim_procedures]'))
    CREATE NONCLUSTERED INDEX [IX_claim_procedures_philhealth_claim_id] ON [dbo].[claim_procedures]([philhealth_claim_id] ASC);
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'IX_claim_procedures_rvs_code' AND object_id = OBJECT_ID(N'[dbo].[claim_procedures]'))
    CREATE NONCLUSTERED INDEX [IX_claim_procedures_rvs_code] ON [dbo].[claim_procedures]([rvs_code] ASC);
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'FK_claim_procedures_philhealth_claim_id' AND parent_object_id = OBJECT_ID(N'[dbo].[claim_procedures]'))
    ALTER TABLE [dbo].[claim_procedures] ADD CONSTRAINT [FK_claim_procedures_philhealth_claim_id] FOREIGN KEY ([philhealth_claim_id]) REFERENCES [dbo].[philhealth_claims]([id]) ON DELETE CASCADE;
