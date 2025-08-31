--
-- PostgreSQL database dump
--

-- Dumped from database version 16.1
-- Dumped by pg_dump version 16.1

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

ALTER TABLE ONLY public.salary_releases DROP CONSTRAINT salary_releases_released_by_foreign;
ALTER TABLE ONLY public.salary_releases DROP CONSTRAINT salary_releases_employee_id_foreign;
ALTER TABLE ONLY public.salary_releases DROP CONSTRAINT salary_releases_created_by_foreign;
ALTER TABLE ONLY public.salary_releases DROP CONSTRAINT salary_releases_cashflow_entry_id_foreign;
ALTER TABLE ONLY public.role_users DROP CONSTRAINT role_users_user_id_foreign;
ALTER TABLE ONLY public.role_users DROP CONSTRAINT role_users_role_id_foreign;
ALTER TABLE ONLY public.revenue_items DROP CONSTRAINT revenue_items_revenue_id_foreign;
ALTER TABLE ONLY public.revenue_items DROP CONSTRAINT revenue_items_project_id_foreign;
ALTER TABLE ONLY public.project_timelines DROP CONSTRAINT project_timelines_project_id_foreign;
ALTER TABLE ONLY public.project_revenues DROP CONSTRAINT project_revenues_project_id_foreign;
ALTER TABLE ONLY public.project_profit_analyses DROP CONSTRAINT project_profit_analyses_project_id_foreign;
ALTER TABLE ONLY public.project_payment_schedules DROP CONSTRAINT project_payment_schedules_project_id_foreign;
ALTER TABLE ONLY public.project_payment_schedules DROP CONSTRAINT project_payment_schedules_billing_id_foreign;
ALTER TABLE ONLY public.project_folders DROP CONSTRAINT project_folders_project_id_foreign;
ALTER TABLE ONLY public.project_folders DROP CONSTRAINT project_folders_parent_id_foreign;
ALTER TABLE ONLY public.project_expenses DROP CONSTRAINT project_expenses_user_id_foreign;
ALTER TABLE ONLY public.project_expenses DROP CONSTRAINT project_expenses_project_id_foreign;
ALTER TABLE ONLY public.project_documents DROP CONSTRAINT project_documents_uploaded_by_foreign;
ALTER TABLE ONLY public.project_documents DROP CONSTRAINT project_documents_project_id_foreign;
ALTER TABLE ONLY public.project_billings DROP CONSTRAINT project_billings_project_id_foreign;
ALTER TABLE ONLY public.project_billings DROP CONSTRAINT project_billings_parent_schedule_id_foreign;
ALTER TABLE ONLY public.project_billings DROP CONSTRAINT project_billings_billing_batch_id_foreign;
ALTER TABLE ONLY public.project_activities DROP CONSTRAINT project_activities_user_id_foreign;
ALTER TABLE ONLY public.project_activities DROP CONSTRAINT project_activities_project_id_foreign;
ALTER TABLE ONLY public.import_logs DROP CONSTRAINT import_logs_user_id_foreign;
ALTER TABLE ONLY public.expense_modification_approvals DROP CONSTRAINT expense_modification_approvals_requested_by_foreign;
ALTER TABLE ONLY public.expense_modification_approvals DROP CONSTRAINT expense_modification_approvals_expense_id_foreign;
ALTER TABLE ONLY public.expense_modification_approvals DROP CONSTRAINT expense_modification_approvals_approved_by_foreign;
ALTER TABLE ONLY public.expense_approvals DROP CONSTRAINT expense_approvals_expense_id_foreign;
ALTER TABLE ONLY public.expense_approvals DROP CONSTRAINT expense_approvals_approver_id_foreign;
ALTER TABLE ONLY public.employee_work_schedules DROP CONSTRAINT employee_work_schedules_employee_id_foreign;
ALTER TABLE ONLY public.employee_custom_off_days DROP CONSTRAINT employee_custom_off_days_employee_id_foreign;
ALTER TABLE ONLY public.daily_salaries DROP CONSTRAINT daily_salaries_salary_release_id_foreign;
ALTER TABLE ONLY public.daily_salaries DROP CONSTRAINT daily_salaries_employee_id_foreign;
ALTER TABLE ONLY public.daily_salaries DROP CONSTRAINT daily_salaries_created_by_foreign;
ALTER TABLE ONLY public.cashflow_entries DROP CONSTRAINT cashflow_entries_project_id_foreign;
ALTER TABLE ONLY public.cashflow_entries DROP CONSTRAINT cashflow_entries_created_by_foreign;
ALTER TABLE ONLY public.cashflow_entries DROP CONSTRAINT cashflow_entries_confirmed_by_foreign;
ALTER TABLE ONLY public.cashflow_entries DROP CONSTRAINT cashflow_entries_category_id_foreign;
ALTER TABLE ONLY public.billing_status_logs DROP CONSTRAINT billing_status_logs_user_id_foreign;
ALTER TABLE ONLY public.billing_status_logs DROP CONSTRAINT billing_status_logs_billing_batch_id_foreign;
ALTER TABLE ONLY public.billing_documents DROP CONSTRAINT billing_documents_uploaded_by_foreign;
ALTER TABLE ONLY public.billing_documents DROP CONSTRAINT billing_documents_billing_batch_id_foreign;
DROP INDEX public.unique_employee_work_date_not_deleted;
DROP INDEX public.sync_logs_syncable_type_syncable_id_index;
DROP INDEX public.sync_logs_status_index;
DROP INDEX public.sync_logs_created_at_index;
DROP INDEX public.sessions_user_id_index;
DROP INDEX public.sessions_last_activity_index;
DROP INDEX public.salary_releases_status_index;
DROP INDEX public.salary_releases_release_date_index;
DROP INDEX public.salary_releases_release_code_index;
DROP INDEX public.salary_releases_employee_id_period_start_period_end_index;
DROP INDEX public.projects_last_billing_date_index;
DROP INDEX public.projects_billing_status_index;
DROP INDEX public.project_payment_schedules_project_id_termin_number_index;
DROP INDEX public.project_payment_schedules_project_id_status_index;
DROP INDEX public.project_payment_schedules_due_date_status_index;
DROP INDEX public.project_locations_usage_count_index;
DROP INDEX public.project_locations_name_index;
DROP INDEX public.project_folders_project_id_folder_path_index;
DROP INDEX public.project_folders_parent_id_index;
DROP INDEX public.project_documents_sync_status_index;
DROP INDEX public.project_documents_last_sync_at_index;
DROP INDEX public.project_clients_usage_count_last_used_at_index;
DROP INDEX public.project_clients_name_index;
DROP INDEX public.project_billings_project_id_payment_type_index;
DROP INDEX public.project_billings_payment_type_termin_number_index;
DROP INDEX public.project_billings_billing_batch_id_index;
DROP INDEX public.jobs_queue_index;
DROP INDEX public.idx_off_date;
DROP INDEX public.idx_employee_period;
DROP INDEX public.idx_employee_active;
DROP INDEX public.idx_effective_period;
DROP INDEX public.expense_modification_approvals_status_created_at_index;
DROP INDEX public.expense_modification_approvals_requested_by_status_index;
DROP INDEX public.expense_modification_approvals_expense_id_action_type_index;
DROP INDEX public.expense_modification_approvals_approved_by_approved_at_index;
DROP INDEX public.employees_status_index;
DROP INDEX public.employees_employment_type_index;
DROP INDEX public.employees_employee_code_index;
DROP INDEX public.employees_email_index;
DROP INDEX public.employees_department_index;
DROP INDEX public.daily_salaries_work_date_index;
DROP INDEX public.daily_salaries_status_index;
DROP INDEX public.daily_salaries_salary_release_id_index;
DROP INDEX public.daily_salaries_employee_id_work_date_index;
DROP INDEX public.cashflow_entries_transaction_date_type_index;
DROP INDEX public.cashflow_entries_status_transaction_date_index;
DROP INDEX public.cashflow_entries_reference_type_reference_id_index;
DROP INDEX public.cashflow_entries_project_id_transaction_date_index;
DROP INDEX public.cashflow_categories_sort_order_index;
DROP INDEX public.cashflow_categories_group_index;
DROP INDEX public.billing_status_logs_status_index;
DROP INDEX public.billing_status_logs_billing_batch_id_created_at_index;
DROP INDEX public.billing_documents_document_type_index;
DROP INDEX public.billing_documents_billing_batch_id_stage_index;
DROP INDEX public.billing_batches_status_index;
DROP INDEX public.billing_batches_billing_date_index;
DROP INDEX public.billing_batches_batch_code_index;
ALTER TABLE ONLY public.users DROP CONSTRAINT users_pkey;
ALTER TABLE ONLY public.users DROP CONSTRAINT users_email_unique;
ALTER TABLE ONLY public.employee_custom_off_days DROP CONSTRAINT unique_employee_off_date;
ALTER TABLE ONLY public.sync_logs DROP CONSTRAINT sync_logs_pkey;
ALTER TABLE ONLY public.settings DROP CONSTRAINT settings_pkey;
ALTER TABLE ONLY public.settings DROP CONSTRAINT settings_key_unique;
ALTER TABLE ONLY public.sessions DROP CONSTRAINT sessions_pkey;
ALTER TABLE ONLY public.salary_releases DROP CONSTRAINT salary_releases_release_code_unique;
ALTER TABLE ONLY public.salary_releases DROP CONSTRAINT salary_releases_pkey;
ALTER TABLE ONLY public.roles DROP CONSTRAINT roles_pkey;
ALTER TABLE ONLY public.roles DROP CONSTRAINT roles_name_unique;
ALTER TABLE ONLY public.role_users DROP CONSTRAINT role_users_user_id_role_id_unique;
ALTER TABLE ONLY public.role_users DROP CONSTRAINT role_users_pkey;
ALTER TABLE ONLY public.revenue_items DROP CONSTRAINT revenue_items_pkey;
ALTER TABLE ONLY public.projects DROP CONSTRAINT projects_pkey;
ALTER TABLE ONLY public.projects DROP CONSTRAINT projects_code_unique;
ALTER TABLE ONLY public.project_timelines DROP CONSTRAINT project_timelines_pkey;
ALTER TABLE ONLY public.project_revenues DROP CONSTRAINT project_revenues_pkey;
ALTER TABLE ONLY public.project_profit_analyses DROP CONSTRAINT project_profit_analyses_pkey;
ALTER TABLE ONLY public.project_payment_schedules DROP CONSTRAINT project_payment_schedules_project_id_termin_number_unique;
ALTER TABLE ONLY public.project_payment_schedules DROP CONSTRAINT project_payment_schedules_pkey;
ALTER TABLE ONLY public.project_locations DROP CONSTRAINT project_locations_pkey;
ALTER TABLE ONLY public.project_locations DROP CONSTRAINT project_locations_name_unique;
ALTER TABLE ONLY public.project_folders DROP CONSTRAINT project_folders_pkey;
ALTER TABLE ONLY public.project_expenses DROP CONSTRAINT project_expenses_pkey;
ALTER TABLE ONLY public.project_documents DROP CONSTRAINT project_documents_pkey;
ALTER TABLE ONLY public.project_clients DROP CONSTRAINT project_clients_pkey;
ALTER TABLE ONLY public.project_clients DROP CONSTRAINT project_clients_name_unique;
ALTER TABLE ONLY public.project_billings DROP CONSTRAINT project_billings_pkey;
ALTER TABLE ONLY public.project_activities DROP CONSTRAINT project_activities_pkey;
ALTER TABLE ONLY public.password_reset_tokens DROP CONSTRAINT password_reset_tokens_pkey;
ALTER TABLE ONLY public.migrations DROP CONSTRAINT migrations_pkey;
ALTER TABLE ONLY public.jobs DROP CONSTRAINT jobs_pkey;
ALTER TABLE ONLY public.job_batches DROP CONSTRAINT job_batches_pkey;
ALTER TABLE ONLY public.import_logs DROP CONSTRAINT import_logs_pkey;
ALTER TABLE ONLY public.failed_jobs DROP CONSTRAINT failed_jobs_uuid_unique;
ALTER TABLE ONLY public.failed_jobs DROP CONSTRAINT failed_jobs_pkey;
ALTER TABLE ONLY public.expense_modification_approvals DROP CONSTRAINT expense_modification_approvals_pkey;
ALTER TABLE ONLY public.expense_approvals DROP CONSTRAINT expense_approvals_pkey;
ALTER TABLE ONLY public.employees DROP CONSTRAINT employees_pkey;
ALTER TABLE ONLY public.employees DROP CONSTRAINT employees_employee_code_unique;
ALTER TABLE ONLY public.employee_work_schedules DROP CONSTRAINT employee_work_schedules_pkey;
ALTER TABLE ONLY public.employee_custom_off_days DROP CONSTRAINT employee_custom_off_days_pkey;
ALTER TABLE ONLY public.daily_salaries DROP CONSTRAINT daily_salaries_pkey;
ALTER TABLE ONLY public.companies DROP CONSTRAINT companies_pkey;
ALTER TABLE ONLY public.cashflow_entries DROP CONSTRAINT cashflow_entries_pkey;
ALTER TABLE ONLY public.cashflow_categories DROP CONSTRAINT cashflow_categories_pkey;
ALTER TABLE ONLY public.cashflow_categories DROP CONSTRAINT cashflow_categories_code_unique;
ALTER TABLE ONLY public.cache DROP CONSTRAINT cache_pkey;
ALTER TABLE ONLY public.cache_locks DROP CONSTRAINT cache_locks_pkey;
ALTER TABLE ONLY public.billing_status_logs DROP CONSTRAINT billing_status_logs_pkey;
ALTER TABLE ONLY public.billing_documents DROP CONSTRAINT billing_documents_pkey;
ALTER TABLE ONLY public.billing_batches DROP CONSTRAINT billing_batches_pkey;
ALTER TABLE ONLY public.billing_batches DROP CONSTRAINT billing_batches_batch_code_unique;
ALTER TABLE public.users ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.sync_logs ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.settings ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.salary_releases ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.roles ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.role_users ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.revenue_items ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.projects ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.project_timelines ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.project_revenues ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.project_profit_analyses ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.project_payment_schedules ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.project_locations ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.project_folders ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.project_expenses ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.project_documents ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.project_clients ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.project_billings ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.project_activities ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.migrations ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.jobs ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.import_logs ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.failed_jobs ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.expense_modification_approvals ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.expense_approvals ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.employees ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.employee_work_schedules ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.employee_custom_off_days ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.daily_salaries ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.companies ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.cashflow_entries ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.cashflow_categories ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.billing_status_logs ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.billing_documents ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.billing_batches ALTER COLUMN id DROP DEFAULT;
DROP SEQUENCE public.users_id_seq;
DROP TABLE public.users;
DROP SEQUENCE public.sync_logs_id_seq;
DROP TABLE public.sync_logs;
DROP SEQUENCE public.settings_id_seq;
DROP TABLE public.settings;
DROP TABLE public.sessions;
DROP SEQUENCE public.salary_releases_id_seq;
DROP TABLE public.salary_releases;
DROP SEQUENCE public.roles_id_seq;
DROP TABLE public.roles;
DROP SEQUENCE public.role_users_id_seq;
DROP TABLE public.role_users;
DROP SEQUENCE public.revenue_items_id_seq;
DROP TABLE public.revenue_items;
DROP SEQUENCE public.projects_id_seq;
DROP TABLE public.projects;
DROP SEQUENCE public.project_timelines_id_seq;
DROP TABLE public.project_timelines;
DROP SEQUENCE public.project_revenues_id_seq;
DROP TABLE public.project_revenues;
DROP SEQUENCE public.project_profit_analyses_id_seq;
DROP TABLE public.project_profit_analyses;
DROP SEQUENCE public.project_payment_schedules_id_seq;
DROP TABLE public.project_payment_schedules;
DROP SEQUENCE public.project_locations_id_seq;
DROP TABLE public.project_locations;
DROP SEQUENCE public.project_folders_id_seq;
DROP TABLE public.project_folders;
DROP SEQUENCE public.project_expenses_id_seq;
DROP TABLE public.project_expenses;
DROP SEQUENCE public.project_documents_id_seq;
DROP TABLE public.project_documents;
DROP SEQUENCE public.project_clients_id_seq;
DROP TABLE public.project_clients;
DROP SEQUENCE public.project_billings_id_seq;
DROP TABLE public.project_billings;
DROP SEQUENCE public.project_activities_id_seq;
DROP TABLE public.project_activities;
DROP TABLE public.password_reset_tokens;
DROP SEQUENCE public.migrations_id_seq;
DROP TABLE public.migrations;
DROP SEQUENCE public.jobs_id_seq;
DROP TABLE public.jobs;
DROP TABLE public.job_batches;
DROP SEQUENCE public.import_logs_id_seq;
DROP TABLE public.import_logs;
DROP SEQUENCE public.failed_jobs_id_seq;
DROP TABLE public.failed_jobs;
DROP SEQUENCE public.expense_modification_approvals_id_seq;
DROP TABLE public.expense_modification_approvals;
DROP SEQUENCE public.expense_approvals_id_seq;
DROP TABLE public.expense_approvals;
DROP SEQUENCE public.employees_id_seq;
DROP TABLE public.employees;
DROP SEQUENCE public.employee_work_schedules_id_seq;
DROP TABLE public.employee_work_schedules;
DROP SEQUENCE public.employee_custom_off_days_id_seq;
DROP TABLE public.employee_custom_off_days;
DROP SEQUENCE public.daily_salaries_id_seq;
DROP TABLE public.daily_salaries;
DROP SEQUENCE public.companies_id_seq;
DROP TABLE public.companies;
DROP SEQUENCE public.cashflow_entries_id_seq;
DROP TABLE public.cashflow_entries;
DROP SEQUENCE public.cashflow_categories_id_seq;
DROP TABLE public.cashflow_categories;
DROP TABLE public.cache_locks;
DROP TABLE public.cache;
DROP SEQUENCE public.billing_status_logs_id_seq;
DROP TABLE public.billing_status_logs;
DROP SEQUENCE public.billing_documents_id_seq;
DROP TABLE public.billing_documents;
DROP SEQUENCE public.billing_batches_id_seq;
DROP TABLE public.billing_batches;
SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: billing_batches; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.billing_batches (
    id bigint NOT NULL,
    batch_code character varying(255) NOT NULL,
    invoice_number character varying(255),
    tax_invoice_number character varying(255),
    sp_number character varying(255),
    total_base_amount numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    pph_rate numeric(5,2) DEFAULT '2'::numeric NOT NULL,
    pph_amount numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    ppn_rate numeric(5,2) DEFAULT '11'::numeric NOT NULL,
    ppn_amount numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    total_billing_amount numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    total_received_amount numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    status character varying(255) DEFAULT 'draft'::character varying NOT NULL,
    billing_date date NOT NULL,
    sent_date timestamp(0) without time zone,
    area_verification_date timestamp(0) without time zone,
    area_revision_date timestamp(0) without time zone,
    regional_verification_date timestamp(0) without time zone,
    regional_revision_date timestamp(0) without time zone,
    payment_entry_date timestamp(0) without time zone,
    paid_date timestamp(0) without time zone,
    notes text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    client_type character varying(255) DEFAULT 'non_wapu'::character varying NOT NULL,
    CONSTRAINT billing_batches_client_type_check CHECK (((client_type)::text = ANY ((ARRAY['wapu'::character varying, 'non_wapu'::character varying])::text[]))),
    CONSTRAINT billing_batches_status_check CHECK (((status)::text = ANY ((ARRAY['draft'::character varying, 'sent'::character varying, 'area_verification'::character varying, 'area_revision'::character varying, 'regional_verification'::character varying, 'regional_revision'::character varying, 'payment_entry_ho'::character varying, 'paid'::character varying, 'cancelled'::character varying])::text[])))
);


ALTER TABLE public.billing_batches OWNER TO postgres;

--
-- Name: billing_batches_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.billing_batches_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.billing_batches_id_seq OWNER TO postgres;

--
-- Name: billing_batches_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.billing_batches_id_seq OWNED BY public.billing_batches.id;


--
-- Name: billing_documents; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.billing_documents (
    id bigint NOT NULL,
    billing_batch_id bigint NOT NULL,
    stage character varying(255) NOT NULL,
    document_type character varying(255),
    file_name character varying(255) NOT NULL,
    file_path character varying(255) NOT NULL,
    file_size character varying(255),
    mime_type character varying(255),
    description text,
    uploaded_by bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT billing_documents_stage_check CHECK (((stage)::text = ANY ((ARRAY['initial'::character varying, 'area_revision'::character varying, 'regional_revision'::character varying, 'supporting_document'::character varying])::text[])))
);


ALTER TABLE public.billing_documents OWNER TO postgres;

--
-- Name: billing_documents_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.billing_documents_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.billing_documents_id_seq OWNER TO postgres;

--
-- Name: billing_documents_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.billing_documents_id_seq OWNED BY public.billing_documents.id;


--
-- Name: billing_status_logs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.billing_status_logs (
    id bigint NOT NULL,
    billing_batch_id bigint NOT NULL,
    status character varying(255) NOT NULL,
    notes text,
    user_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT billing_status_logs_status_check CHECK (((status)::text = ANY ((ARRAY['draft'::character varying, 'sent'::character varying, 'area_verification'::character varying, 'area_revision'::character varying, 'regional_verification'::character varying, 'regional_revision'::character varying, 'payment_entry_ho'::character varying, 'paid'::character varying, 'cancelled'::character varying])::text[])))
);


ALTER TABLE public.billing_status_logs OWNER TO postgres;

--
-- Name: billing_status_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.billing_status_logs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.billing_status_logs_id_seq OWNER TO postgres;

--
-- Name: billing_status_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.billing_status_logs_id_seq OWNED BY public.billing_status_logs.id;


--
-- Name: cache; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache OWNER TO postgres;

--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO postgres;

--
-- Name: cashflow_categories; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cashflow_categories (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    type character varying(255) NOT NULL,
    code character varying(255) NOT NULL,
    description text,
    is_active boolean DEFAULT true NOT NULL,
    is_system boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    "group" character varying(50),
    sort_order integer DEFAULT 0 NOT NULL,
    CONSTRAINT cashflow_categories_type_check CHECK (((type)::text = ANY ((ARRAY['income'::character varying, 'expense'::character varying])::text[])))
);


ALTER TABLE public.cashflow_categories OWNER TO postgres;

--
-- Name: cashflow_categories_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.cashflow_categories_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.cashflow_categories_id_seq OWNER TO postgres;

--
-- Name: cashflow_categories_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.cashflow_categories_id_seq OWNED BY public.cashflow_categories.id;


--
-- Name: cashflow_entries; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cashflow_entries (
    id bigint NOT NULL,
    reference_type character varying(255) NOT NULL,
    reference_id bigint,
    project_id bigint,
    category_id bigint NOT NULL,
    transaction_date date NOT NULL,
    description text NOT NULL,
    amount numeric(15,2) NOT NULL,
    type character varying(255) NOT NULL,
    payment_method character varying(255),
    account_code character varying(255),
    notes text,
    created_by bigint NOT NULL,
    status character varying(255) DEFAULT 'confirmed'::character varying NOT NULL,
    confirmed_at timestamp(0) without time zone,
    confirmed_by bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT cashflow_entries_reference_type_check CHECK (((reference_type)::text = ANY ((ARRAY['billing'::character varying, 'expense'::character varying, 'manual'::character varying, 'adjustment'::character varying])::text[]))),
    CONSTRAINT cashflow_entries_status_check CHECK (((status)::text = ANY ((ARRAY['pending'::character varying, 'confirmed'::character varying, 'cancelled'::character varying])::text[]))),
    CONSTRAINT cashflow_entries_type_check CHECK (((type)::text = ANY ((ARRAY['income'::character varying, 'expense'::character varying])::text[])))
);


ALTER TABLE public.cashflow_entries OWNER TO postgres;

--
-- Name: cashflow_entries_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.cashflow_entries_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.cashflow_entries_id_seq OWNER TO postgres;

--
-- Name: cashflow_entries_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.cashflow_entries_id_seq OWNED BY public.cashflow_entries.id;


--
-- Name: companies; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.companies (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    address text,
    phone character varying(255),
    email character varying(255),
    contact_person character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    is_active boolean DEFAULT false NOT NULL
);


ALTER TABLE public.companies OWNER TO postgres;

--
-- Name: companies_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.companies_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.companies_id_seq OWNER TO postgres;

--
-- Name: companies_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.companies_id_seq OWNED BY public.companies.id;


--
-- Name: daily_salaries; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.daily_salaries (
    id bigint NOT NULL,
    employee_id bigint NOT NULL,
    work_date date NOT NULL,
    amount numeric(10,2) NOT NULL,
    hours_worked numeric(4,2) DEFAULT '8'::numeric NOT NULL,
    overtime_hours numeric(4,2) DEFAULT '0'::numeric NOT NULL,
    overtime_rate numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    notes text,
    status character varying(255) DEFAULT 'draft'::character varying NOT NULL,
    created_by bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    basic_salary numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    meal_allowance numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    attendance_bonus numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    phone_allowance numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    transport_allowance numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    attendance_status character varying(255) DEFAULT 'present'::character varying NOT NULL,
    check_in_time time(0) without time zone,
    check_out_time time(0) without time zone,
    deductions numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    total_amount numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    deleted_at timestamp(0) without time zone,
    overtime_amount numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    salary_release_id bigint,
    CONSTRAINT daily_salaries_attendance_status_check CHECK (((attendance_status)::text = ANY ((ARRAY['present'::character varying, 'late'::character varying, 'absent'::character varying, 'sick'::character varying, 'leave'::character varying])::text[]))),
    CONSTRAINT daily_salaries_status_check CHECK (((status)::text = ANY ((ARRAY['draft'::character varying, 'confirmed'::character varying])::text[])))
);


ALTER TABLE public.daily_salaries OWNER TO postgres;

--
-- Name: COLUMN daily_salaries.amount; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.daily_salaries.amount IS 'Gaji harian';


--
-- Name: COLUMN daily_salaries.hours_worked; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.daily_salaries.hours_worked IS 'Jam kerja';


--
-- Name: COLUMN daily_salaries.overtime_hours; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.daily_salaries.overtime_hours IS 'Jam lembur';


--
-- Name: COLUMN daily_salaries.overtime_rate; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.daily_salaries.overtime_rate IS 'Rate lembur per jam';


--
-- Name: COLUMN daily_salaries.basic_salary; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.daily_salaries.basic_salary IS 'Gaji pokok harian';


--
-- Name: COLUMN daily_salaries.meal_allowance; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.daily_salaries.meal_allowance IS 'Uang makan';


--
-- Name: COLUMN daily_salaries.attendance_bonus; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.daily_salaries.attendance_bonus IS 'Uang absen (bonus jika tepat waktu)';


--
-- Name: COLUMN daily_salaries.phone_allowance; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.daily_salaries.phone_allowance IS 'Uang pulsa';


--
-- Name: COLUMN daily_salaries.transport_allowance; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.daily_salaries.transport_allowance IS 'Uang transport';


--
-- Name: COLUMN daily_salaries.attendance_status; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.daily_salaries.attendance_status IS 'Status kehadiran';


--
-- Name: COLUMN daily_salaries.check_in_time; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.daily_salaries.check_in_time IS 'Waktu masuk';


--
-- Name: COLUMN daily_salaries.check_out_time; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.daily_salaries.check_out_time IS 'Waktu pulang';


--
-- Name: COLUMN daily_salaries.deductions; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.daily_salaries.deductions IS 'Potongan (telat, dll)';


--
-- Name: COLUMN daily_salaries.total_amount; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.daily_salaries.total_amount IS 'Total gaji harian (calculated)';


--
-- Name: COLUMN daily_salaries.overtime_amount; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.daily_salaries.overtime_amount IS 'Jumlah uang lembur';


--
-- Name: daily_salaries_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.daily_salaries_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.daily_salaries_id_seq OWNER TO postgres;

--
-- Name: daily_salaries_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.daily_salaries_id_seq OWNED BY public.daily_salaries.id;


--
-- Name: employee_custom_off_days; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.employee_custom_off_days (
    id bigint NOT NULL,
    employee_id bigint NOT NULL,
    off_date date NOT NULL,
    reason character varying(255),
    period_month integer NOT NULL,
    period_year integer NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.employee_custom_off_days OWNER TO postgres;

--
-- Name: COLUMN employee_custom_off_days.reason; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.employee_custom_off_days.reason IS 'Alasan libur: cuti, libur custom, dll';


--
-- Name: COLUMN employee_custom_off_days.period_month; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.employee_custom_off_days.period_month IS 'Bulan periode (1-12)';


--
-- Name: COLUMN employee_custom_off_days.period_year; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.employee_custom_off_days.period_year IS 'Tahun periode';


--
-- Name: employee_custom_off_days_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.employee_custom_off_days_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.employee_custom_off_days_id_seq OWNER TO postgres;

--
-- Name: employee_custom_off_days_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.employee_custom_off_days_id_seq OWNED BY public.employee_custom_off_days.id;


--
-- Name: employee_work_schedules; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.employee_work_schedules (
    id bigint NOT NULL,
    employee_id bigint NOT NULL,
    schedule_type character varying(255) DEFAULT 'standard'::character varying NOT NULL,
    work_days_per_month integer,
    standard_off_days json,
    effective_from date NOT NULL,
    effective_until date,
    is_active boolean DEFAULT true NOT NULL,
    notes text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT employee_work_schedules_schedule_type_check CHECK (((schedule_type)::text = ANY ((ARRAY['standard'::character varying, 'custom'::character varying, 'flexible'::character varying])::text[])))
);


ALTER TABLE public.employee_work_schedules OWNER TO postgres;

--
-- Name: COLUMN employee_work_schedules.work_days_per_month; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.employee_work_schedules.work_days_per_month IS 'Jumlah hari kerja per bulan untuk tipe flexible';


--
-- Name: COLUMN employee_work_schedules.standard_off_days; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.employee_work_schedules.standard_off_days IS 'Hari libur tetap: [0,6] untuk Minggu,Sabtu';


--
-- Name: employee_work_schedules_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.employee_work_schedules_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.employee_work_schedules_id_seq OWNER TO postgres;

--
-- Name: employee_work_schedules_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.employee_work_schedules_id_seq OWNED BY public.employee_work_schedules.id;


--
-- Name: employees; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.employees (
    id bigint NOT NULL,
    employee_code character varying(20) NOT NULL,
    name character varying(255) NOT NULL,
    "position" character varying(100),
    department character varying(100),
    hire_date date,
    phone character varying(20),
    address text,
    status character varying(255) DEFAULT 'active'::character varying NOT NULL,
    daily_rate numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    notes text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    email character varying(255),
    birth_date date,
    gender character varying(255),
    id_number character varying(50),
    emergency_contact_name character varying(255),
    emergency_contact_phone character varying(20),
    emergency_contact_relation character varying(50),
    employment_type character varying(50) DEFAULT 'permanent'::character varying NOT NULL,
    contract_end_date date,
    bank_name character varying(100),
    bank_account_number character varying(50),
    bank_account_name character varying(255),
    avatar character varying(255),
    deleted_at timestamp(0) without time zone,
    CONSTRAINT employees_gender_check CHECK (((gender)::text = ANY ((ARRAY['male'::character varying, 'female'::character varying])::text[]))),
    CONSTRAINT employees_status_check CHECK (((status)::text = ANY ((ARRAY['active'::character varying, 'inactive'::character varying])::text[])))
);


ALTER TABLE public.employees OWNER TO postgres;

--
-- Name: COLUMN employees.daily_rate; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.employees.daily_rate IS 'Default gaji harian';


--
-- Name: COLUMN employees.id_number; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.employees.id_number IS 'NIK/KTP';


--
-- Name: COLUMN employees.employment_type; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.employees.employment_type IS 'permanent, contract, freelance';


--
-- Name: COLUMN employees.avatar; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.employees.avatar IS 'Profile photo path';


--
-- Name: employees_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.employees_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.employees_id_seq OWNER TO postgres;

--
-- Name: employees_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.employees_id_seq OWNED BY public.employees.id;


--
-- Name: expense_approvals; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.expense_approvals (
    id bigint NOT NULL,
    expense_id bigint NOT NULL,
    approver_id bigint NOT NULL,
    level character varying(255) NOT NULL,
    status character varying(255) DEFAULT 'pending'::character varying NOT NULL,
    notes text,
    approved_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT expense_approvals_level_check CHECK (((level)::text = ANY ((ARRAY['finance_manager'::character varying, 'project_manager'::character varying, 'direktur'::character varying])::text[]))),
    CONSTRAINT expense_approvals_status_check CHECK (((status)::text = ANY ((ARRAY['pending'::character varying, 'approved'::character varying, 'rejected'::character varying])::text[])))
);


ALTER TABLE public.expense_approvals OWNER TO postgres;

--
-- Name: expense_approvals_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.expense_approvals_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.expense_approvals_id_seq OWNER TO postgres;

--
-- Name: expense_approvals_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.expense_approvals_id_seq OWNED BY public.expense_approvals.id;


--
-- Name: expense_modification_approvals; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.expense_modification_approvals (
    id bigint NOT NULL,
    expense_id bigint NOT NULL,
    action_type character varying(255) NOT NULL,
    requested_by bigint NOT NULL,
    original_data json,
    proposed_data json,
    reason text NOT NULL,
    status character varying(255) DEFAULT 'pending'::character varying NOT NULL,
    approved_by bigint,
    approved_at timestamp(0) without time zone,
    approval_notes text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT expense_modification_approvals_action_type_check CHECK (((action_type)::text = ANY ((ARRAY['edit'::character varying, 'delete'::character varying])::text[]))),
    CONSTRAINT expense_modification_approvals_status_check CHECK (((status)::text = ANY ((ARRAY['pending'::character varying, 'approved'::character varying, 'rejected'::character varying])::text[])))
);


ALTER TABLE public.expense_modification_approvals OWNER TO postgres;

--
-- Name: expense_modification_approvals_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.expense_modification_approvals_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.expense_modification_approvals_id_seq OWNER TO postgres;

--
-- Name: expense_modification_approvals_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.expense_modification_approvals_id_seq OWNED BY public.expense_modification_approvals.id;


--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.failed_jobs OWNER TO postgres;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.failed_jobs_id_seq OWNER TO postgres;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: import_logs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.import_logs (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    file_name character varying(255) NOT NULL,
    file_path character varying(255) NOT NULL,
    status character varying(255) DEFAULT 'processing'::character varying NOT NULL,
    total_rows integer DEFAULT 0 NOT NULL,
    successful_rows integer DEFAULT 0 NOT NULL,
    failed_rows integer DEFAULT 0 NOT NULL,
    error_message text,
    failed_rows_details json,
    completed_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT import_logs_status_check CHECK (((status)::text = ANY ((ARRAY['processing'::character varying, 'completed'::character varying, 'failed'::character varying])::text[])))
);


ALTER TABLE public.import_logs OWNER TO postgres;

--
-- Name: import_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.import_logs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.import_logs_id_seq OWNER TO postgres;

--
-- Name: import_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.import_logs_id_seq OWNED BY public.import_logs.id;


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


ALTER TABLE public.job_batches OWNER TO postgres;

--
-- Name: jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


ALTER TABLE public.jobs OWNER TO postgres;

--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.jobs_id_seq OWNER TO postgres;

--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO postgres;

--
-- Name: project_activities; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.project_activities (
    id bigint NOT NULL,
    project_id bigint NOT NULL,
    user_id bigint NOT NULL,
    activity_type character varying(255) NOT NULL,
    description text NOT NULL,
    changes json,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.project_activities OWNER TO postgres;

--
-- Name: project_activities_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.project_activities_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.project_activities_id_seq OWNER TO postgres;

--
-- Name: project_activities_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.project_activities_id_seq OWNED BY public.project_activities.id;


--
-- Name: project_billings; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.project_billings (
    id bigint NOT NULL,
    project_id bigint NOT NULL,
    billing_date date NOT NULL,
    status character varying(255) DEFAULT 'draft'::character varying NOT NULL,
    notes text,
    paid_date date,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    payment_type character varying(255) DEFAULT 'full'::character varying NOT NULL,
    termin_number integer,
    total_termin integer,
    is_final_termin boolean DEFAULT false NOT NULL,
    parent_schedule_id bigint,
    nilai_jasa numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    nilai_material numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    subtotal numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    ppn_rate numeric(5,2) DEFAULT '11'::numeric NOT NULL,
    ppn_calculation character varying(255) DEFAULT 'normal'::character varying NOT NULL,
    ppn_amount numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    total_amount numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    invoice_number character varying(255),
    sp_number character varying(255),
    tax_invoice_number character varying(255),
    billing_batch_id bigint,
    base_amount numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    pph_amount numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    received_amount numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    CONSTRAINT project_billings_payment_type_check CHECK (((payment_type)::text = ANY ((ARRAY['full'::character varying, 'termin'::character varying])::text[]))),
    CONSTRAINT project_billings_ppn_calculation_check CHECK (((ppn_calculation)::text = ANY ((ARRAY['round_down'::character varying, 'round_up'::character varying, 'normal'::character varying])::text[]))),
    CONSTRAINT project_billings_status_check CHECK (((status)::text = ANY ((ARRAY['draft'::character varying, 'sent'::character varying, 'paid'::character varying, 'overdue'::character varying])::text[])))
);


ALTER TABLE public.project_billings OWNER TO postgres;

--
-- Name: project_billings_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.project_billings_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.project_billings_id_seq OWNER TO postgres;

--
-- Name: project_billings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.project_billings_id_seq OWNED BY public.project_billings.id;


--
-- Name: project_clients; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.project_clients (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    usage_count integer DEFAULT 1 NOT NULL,
    last_used_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.project_clients OWNER TO postgres;

--
-- Name: project_clients_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.project_clients_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.project_clients_id_seq OWNER TO postgres;

--
-- Name: project_clients_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.project_clients_id_seq OWNED BY public.project_clients.id;


--
-- Name: project_documents; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.project_documents (
    id bigint NOT NULL,
    project_id bigint NOT NULL,
    uploaded_by bigint NOT NULL,
    name character varying(255) NOT NULL,
    original_name character varying(255) NOT NULL,
    file_path character varying(255) NOT NULL,
    file_type character varying(255) NOT NULL,
    file_size bigint NOT NULL,
    document_type character varying(255) DEFAULT 'other'::character varying NOT NULL,
    description text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    storage_path character varying(500),
    rclone_path character varying(500),
    sync_status character varying(255) DEFAULT 'pending'::character varying NOT NULL,
    sync_error text,
    last_sync_at timestamp(0) without time zone,
    checksum character varying(64),
    folder_structure json,
    CONSTRAINT project_documents_document_type_check CHECK (((document_type)::text = ANY ((ARRAY['contract'::character varying, 'technical'::character varying, 'financial'::character varying, 'report'::character varying, 'other'::character varying])::text[]))),
    CONSTRAINT project_documents_sync_status_check CHECK (((sync_status)::text = ANY ((ARRAY['pending'::character varying, 'syncing'::character varying, 'synced'::character varying, 'failed'::character varying, 'out_of_sync'::character varying])::text[])))
);


ALTER TABLE public.project_documents OWNER TO postgres;

--
-- Name: project_documents_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.project_documents_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.project_documents_id_seq OWNER TO postgres;

--
-- Name: project_documents_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.project_documents_id_seq OWNED BY public.project_documents.id;


--
-- Name: project_expenses; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.project_expenses (
    id bigint NOT NULL,
    project_id bigint NOT NULL,
    user_id bigint NOT NULL,
    description character varying(255) NOT NULL,
    amount numeric(15,2) NOT NULL,
    expense_date date NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    category character varying(255),
    receipt_number character varying(255),
    vendor character varying(255),
    notes text,
    status character varying(255) DEFAULT 'pending'::character varying NOT NULL,
    CONSTRAINT project_expenses_status_check CHECK (((status)::text = ANY ((ARRAY['draft'::character varying, 'pending'::character varying, 'submitted'::character varying, 'approved'::character varying, 'rejected'::character varying])::text[])))
);


ALTER TABLE public.project_expenses OWNER TO postgres;

--
-- Name: project_expenses_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.project_expenses_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.project_expenses_id_seq OWNER TO postgres;

--
-- Name: project_expenses_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.project_expenses_id_seq OWNED BY public.project_expenses.id;


--
-- Name: project_folders; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.project_folders (
    id bigint NOT NULL,
    project_id bigint NOT NULL,
    folder_name character varying(255) NOT NULL,
    folder_path character varying(500) NOT NULL,
    parent_id bigint,
    folder_type character varying(255) DEFAULT 'custom'::character varying NOT NULL,
    sync_status character varying(255) DEFAULT 'pending'::character varying NOT NULL,
    metadata json,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT project_folders_folder_type_check CHECK (((folder_type)::text = ANY ((ARRAY['root'::character varying, 'category'::character varying, 'subcategory'::character varying, 'custom'::character varying])::text[]))),
    CONSTRAINT project_folders_sync_status_check CHECK (((sync_status)::text = ANY ((ARRAY['pending'::character varying, 'synced'::character varying, 'failed'::character varying, 'out_of_sync'::character varying])::text[])))
);


ALTER TABLE public.project_folders OWNER TO postgres;

--
-- Name: project_folders_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.project_folders_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.project_folders_id_seq OWNER TO postgres;

--
-- Name: project_folders_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.project_folders_id_seq OWNED BY public.project_folders.id;


--
-- Name: project_locations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.project_locations (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    usage_count integer DEFAULT 1 NOT NULL,
    last_used_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.project_locations OWNER TO postgres;

--
-- Name: project_locations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.project_locations_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.project_locations_id_seq OWNER TO postgres;

--
-- Name: project_locations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.project_locations_id_seq OWNED BY public.project_locations.id;


--
-- Name: project_payment_schedules; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.project_payment_schedules (
    id bigint NOT NULL,
    project_id bigint NOT NULL,
    termin_number integer NOT NULL,
    total_termin integer NOT NULL,
    termin_name character varying(255),
    percentage numeric(5,2) NOT NULL,
    amount numeric(15,2) NOT NULL,
    due_date date NOT NULL,
    created_date date DEFAULT '2025-08-25'::date NOT NULL,
    status character varying(255) DEFAULT 'pending'::character varying NOT NULL,
    billing_id bigint,
    description text,
    notes text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT project_payment_schedules_status_check CHECK (((status)::text = ANY ((ARRAY['pending'::character varying, 'billed'::character varying, 'paid'::character varying, 'overdue'::character varying])::text[])))
);


ALTER TABLE public.project_payment_schedules OWNER TO postgres;

--
-- Name: project_payment_schedules_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.project_payment_schedules_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.project_payment_schedules_id_seq OWNER TO postgres;

--
-- Name: project_payment_schedules_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.project_payment_schedules_id_seq OWNED BY public.project_payment_schedules.id;


--
-- Name: project_profit_analyses; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.project_profit_analyses (
    id bigint NOT NULL,
    project_id bigint NOT NULL,
    total_revenue numeric(15,2) NOT NULL,
    total_expenses numeric(15,2) NOT NULL,
    net_profit numeric(15,2) NOT NULL,
    profit_margin numeric(5,2) NOT NULL,
    analysis_notes text,
    improvement_recommendations json,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.project_profit_analyses OWNER TO postgres;

--
-- Name: project_profit_analyses_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.project_profit_analyses_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.project_profit_analyses_id_seq OWNER TO postgres;

--
-- Name: project_profit_analyses_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.project_profit_analyses_id_seq OWNED BY public.project_profit_analyses.id;


--
-- Name: project_revenues; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.project_revenues (
    id bigint NOT NULL,
    project_id bigint NOT NULL,
    total_amount numeric(15,2) NOT NULL,
    net_profit numeric(15,2) NOT NULL,
    profit_margin numeric(5,2) NOT NULL,
    revenue_date date NOT NULL,
    calculation_details text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.project_revenues OWNER TO postgres;

--
-- Name: project_revenues_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.project_revenues_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.project_revenues_id_seq OWNER TO postgres;

--
-- Name: project_revenues_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.project_revenues_id_seq OWNED BY public.project_revenues.id;


--
-- Name: project_timelines; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.project_timelines (
    id bigint NOT NULL,
    project_id bigint NOT NULL,
    milestone character varying(255) NOT NULL,
    description text,
    planned_date date NOT NULL,
    actual_date date,
    status character varying(255) DEFAULT 'planned'::character varying NOT NULL,
    progress_percentage integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT project_timelines_status_check CHECK (((status)::text = ANY ((ARRAY['planned'::character varying, 'in_progress'::character varying, 'completed'::character varying, 'delayed'::character varying])::text[])))
);


ALTER TABLE public.project_timelines OWNER TO postgres;

--
-- Name: project_timelines_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.project_timelines_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.project_timelines_id_seq OWNER TO postgres;

--
-- Name: project_timelines_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.project_timelines_id_seq OWNED BY public.project_timelines.id;


--
-- Name: projects; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.projects (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    planned_budget numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    actual_budget numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    start_date date,
    end_date date,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    code character varying(50) NOT NULL,
    location character varying(255),
    priority character varying(255) DEFAULT 'medium'::character varying NOT NULL,
    notes text,
    status character varying(255) DEFAULT 'planning'::character varying NOT NULL,
    planned_service_value numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    planned_material_value numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    planned_total_value numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    final_service_value numeric(15,2),
    final_material_value numeric(15,2),
    final_total_value numeric(15,2),
    client_type character varying(255) DEFAULT 'non_wapu'::character varying NOT NULL,
    billing_status character varying(255) DEFAULT 'not_billed'::character varying NOT NULL,
    latest_po_number character varying(255),
    latest_sp_number character varying(255),
    latest_invoice_number character varying(255),
    total_billed_amount numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    billing_percentage numeric(5,2) DEFAULT '0'::numeric NOT NULL,
    last_billing_date date,
    type character varying(255) DEFAULT 'other'::character varying NOT NULL,
    client character varying(255),
    CONSTRAINT projects_billing_status_check CHECK (((billing_status)::text = ANY ((ARRAY['not_billed'::character varying, 'partially_billed'::character varying, 'fully_billed'::character varying])::text[]))),
    CONSTRAINT projects_client_type_check CHECK (((client_type)::text = ANY ((ARRAY['non_wapu'::character varying, 'wapu'::character varying])::text[]))),
    CONSTRAINT projects_priority_check CHECK (((priority)::text = ANY ((ARRAY['low'::character varying, 'medium'::character varying, 'high'::character varying, 'urgent'::character varying])::text[]))),
    CONSTRAINT projects_status_check CHECK (((status)::text = ANY ((ARRAY['planning'::character varying, 'in_progress'::character varying, 'completed'::character varying, 'cancelled'::character varying])::text[]))),
    CONSTRAINT projects_type_check CHECK (((type)::text = ANY ((ARRAY['konstruksi'::character varying, 'maintenance'::character varying, 'psb'::character varying, 'other'::character varying])::text[])))
);


ALTER TABLE public.projects OWNER TO postgres;

--
-- Name: COLUMN projects.client_type; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.projects.client_type IS 'Tipe klien: non_wapu (umum) atau wapu (BUMN/Pemerintah)';


--
-- Name: projects_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.projects_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.projects_id_seq OWNER TO postgres;

--
-- Name: projects_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.projects_id_seq OWNED BY public.projects.id;


--
-- Name: revenue_items; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.revenue_items (
    id bigint NOT NULL,
    project_id bigint NOT NULL,
    revenue_id bigint NOT NULL,
    item_name character varying(255) NOT NULL,
    description text,
    amount numeric(15,2) NOT NULL,
    type character varying(255) DEFAULT 'service'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT revenue_items_type_check CHECK (((type)::text = ANY ((ARRAY['service'::character varying, 'product'::character varying, 'other'::character varying])::text[])))
);


ALTER TABLE public.revenue_items OWNER TO postgres;

--
-- Name: revenue_items_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.revenue_items_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.revenue_items_id_seq OWNER TO postgres;

--
-- Name: revenue_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.revenue_items_id_seq OWNED BY public.revenue_items.id;


--
-- Name: role_users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.role_users (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    role_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.role_users OWNER TO postgres;

--
-- Name: role_users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.role_users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.role_users_id_seq OWNER TO postgres;

--
-- Name: role_users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.role_users_id_seq OWNED BY public.role_users.id;


--
-- Name: roles; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.roles (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    description character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.roles OWNER TO postgres;

--
-- Name: roles_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.roles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.roles_id_seq OWNER TO postgres;

--
-- Name: roles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.roles_id_seq OWNED BY public.roles.id;


--
-- Name: salary_releases; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.salary_releases (
    id bigint NOT NULL,
    employee_id bigint NOT NULL,
    release_code character varying(50) NOT NULL,
    period_start date NOT NULL,
    period_end date NOT NULL,
    total_days integer DEFAULT 0 NOT NULL,
    total_amount numeric(12,2) NOT NULL,
    deductions numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    net_amount numeric(12,2) NOT NULL,
    release_date date,
    status character varying(255) DEFAULT 'draft'::character varying NOT NULL,
    notes text,
    cashflow_entry_id bigint,
    created_by bigint NOT NULL,
    released_by bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    released_at timestamp(0) without time zone,
    paid_at timestamp(0) without time zone,
    CONSTRAINT salary_releases_status_check CHECK (((status)::text = ANY ((ARRAY['draft'::character varying, 'released'::character varying, 'paid'::character varying])::text[])))
);


ALTER TABLE public.salary_releases OWNER TO postgres;

--
-- Name: COLUMN salary_releases.release_code; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.salary_releases.release_code IS 'Kode rilis gaji';


--
-- Name: COLUMN salary_releases.period_start; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.salary_releases.period_start IS 'Tanggal mulai periode';


--
-- Name: COLUMN salary_releases.period_end; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.salary_releases.period_end IS 'Tanggal akhir periode';


--
-- Name: COLUMN salary_releases.total_days; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.salary_releases.total_days IS 'Total hari kerja';


--
-- Name: COLUMN salary_releases.total_amount; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.salary_releases.total_amount IS 'Total gaji sebelum potongan';


--
-- Name: COLUMN salary_releases.deductions; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.salary_releases.deductions IS 'Total potongan';


--
-- Name: COLUMN salary_releases.net_amount; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.salary_releases.net_amount IS 'Gaji bersih setelah potongan';


--
-- Name: COLUMN salary_releases.release_date; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.salary_releases.release_date IS 'Tanggal rilis gaji';


--
-- Name: salary_releases_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.salary_releases_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.salary_releases_id_seq OWNER TO postgres;

--
-- Name: salary_releases_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.salary_releases_id_seq OWNED BY public.salary_releases.id;


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO postgres;

--
-- Name: settings; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.settings (
    id bigint NOT NULL,
    key character varying(255) NOT NULL,
    value text NOT NULL,
    description character varying(255),
    type character varying(255) DEFAULT 'string'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.settings OWNER TO postgres;

--
-- Name: settings_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.settings_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.settings_id_seq OWNER TO postgres;

--
-- Name: settings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.settings_id_seq OWNED BY public.settings.id;


--
-- Name: sync_logs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sync_logs (
    id bigint NOT NULL,
    syncable_type character varying(50) NOT NULL,
    syncable_id bigint NOT NULL,
    action character varying(255) NOT NULL,
    status character varying(255) NOT NULL,
    source_path character varying(500),
    destination_path character varying(500),
    file_size bigint,
    duration_ms integer,
    error_message text,
    rclone_output text,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT sync_logs_action_check CHECK (((action)::text = ANY ((ARRAY['upload'::character varying, 'download'::character varying, 'delete'::character varying, 'check'::character varying])::text[]))),
    CONSTRAINT sync_logs_status_check CHECK (((status)::text = ANY ((ARRAY['success'::character varying, 'failed'::character varying, 'skipped'::character varying])::text[])))
);


ALTER TABLE public.sync_logs OWNER TO postgres;

--
-- Name: sync_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.sync_logs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.sync_logs_id_seq OWNER TO postgres;

--
-- Name: sync_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.sync_logs_id_seq OWNED BY public.sync_logs.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    company_name character varying(255),
    company_address text,
    company_phone character varying(255),
    company_email character varying(255)
);


ALTER TABLE public.users OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: billing_batches id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.billing_batches ALTER COLUMN id SET DEFAULT nextval('public.billing_batches_id_seq'::regclass);


--
-- Name: billing_documents id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.billing_documents ALTER COLUMN id SET DEFAULT nextval('public.billing_documents_id_seq'::regclass);


--
-- Name: billing_status_logs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.billing_status_logs ALTER COLUMN id SET DEFAULT nextval('public.billing_status_logs_id_seq'::regclass);


--
-- Name: cashflow_categories id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cashflow_categories ALTER COLUMN id SET DEFAULT nextval('public.cashflow_categories_id_seq'::regclass);


--
-- Name: cashflow_entries id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cashflow_entries ALTER COLUMN id SET DEFAULT nextval('public.cashflow_entries_id_seq'::regclass);


--
-- Name: companies id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.companies ALTER COLUMN id SET DEFAULT nextval('public.companies_id_seq'::regclass);


--
-- Name: daily_salaries id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.daily_salaries ALTER COLUMN id SET DEFAULT nextval('public.daily_salaries_id_seq'::regclass);


--
-- Name: employee_custom_off_days id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.employee_custom_off_days ALTER COLUMN id SET DEFAULT nextval('public.employee_custom_off_days_id_seq'::regclass);


--
-- Name: employee_work_schedules id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.employee_work_schedules ALTER COLUMN id SET DEFAULT nextval('public.employee_work_schedules_id_seq'::regclass);


--
-- Name: employees id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.employees ALTER COLUMN id SET DEFAULT nextval('public.employees_id_seq'::regclass);


--
-- Name: expense_approvals id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.expense_approvals ALTER COLUMN id SET DEFAULT nextval('public.expense_approvals_id_seq'::regclass);


--
-- Name: expense_modification_approvals id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.expense_modification_approvals ALTER COLUMN id SET DEFAULT nextval('public.expense_modification_approvals_id_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: import_logs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.import_logs ALTER COLUMN id SET DEFAULT nextval('public.import_logs_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: project_activities id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_activities ALTER COLUMN id SET DEFAULT nextval('public.project_activities_id_seq'::regclass);


--
-- Name: project_billings id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_billings ALTER COLUMN id SET DEFAULT nextval('public.project_billings_id_seq'::regclass);


--
-- Name: project_clients id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_clients ALTER COLUMN id SET DEFAULT nextval('public.project_clients_id_seq'::regclass);


--
-- Name: project_documents id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_documents ALTER COLUMN id SET DEFAULT nextval('public.project_documents_id_seq'::regclass);


--
-- Name: project_expenses id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_expenses ALTER COLUMN id SET DEFAULT nextval('public.project_expenses_id_seq'::regclass);


--
-- Name: project_folders id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_folders ALTER COLUMN id SET DEFAULT nextval('public.project_folders_id_seq'::regclass);


--
-- Name: project_locations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_locations ALTER COLUMN id SET DEFAULT nextval('public.project_locations_id_seq'::regclass);


--
-- Name: project_payment_schedules id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_payment_schedules ALTER COLUMN id SET DEFAULT nextval('public.project_payment_schedules_id_seq'::regclass);


--
-- Name: project_profit_analyses id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_profit_analyses ALTER COLUMN id SET DEFAULT nextval('public.project_profit_analyses_id_seq'::regclass);


--
-- Name: project_revenues id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_revenues ALTER COLUMN id SET DEFAULT nextval('public.project_revenues_id_seq'::regclass);


--
-- Name: project_timelines id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_timelines ALTER COLUMN id SET DEFAULT nextval('public.project_timelines_id_seq'::regclass);


--
-- Name: projects id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.projects ALTER COLUMN id SET DEFAULT nextval('public.projects_id_seq'::regclass);


--
-- Name: revenue_items id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.revenue_items ALTER COLUMN id SET DEFAULT nextval('public.revenue_items_id_seq'::regclass);


--
-- Name: role_users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_users ALTER COLUMN id SET DEFAULT nextval('public.role_users_id_seq'::regclass);


--
-- Name: roles id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles ALTER COLUMN id SET DEFAULT nextval('public.roles_id_seq'::regclass);


--
-- Name: salary_releases id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.salary_releases ALTER COLUMN id SET DEFAULT nextval('public.salary_releases_id_seq'::regclass);


--
-- Name: settings id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.settings ALTER COLUMN id SET DEFAULT nextval('public.settings_id_seq'::regclass);


--
-- Name: sync_logs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sync_logs ALTER COLUMN id SET DEFAULT nextval('public.sync_logs_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Data for Name: billing_batches; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.billing_batches (id, batch_code, invoice_number, tax_invoice_number, sp_number, total_base_amount, pph_rate, pph_amount, ppn_rate, ppn_amount, total_billing_amount, total_received_amount, status, billing_date, sent_date, area_verification_date, area_revision_date, regional_verification_date, regional_revision_date, payment_entry_date, paid_date, notes, created_at, updated_at, client_type) FROM stdin;
\.


--
-- Data for Name: billing_documents; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.billing_documents (id, billing_batch_id, stage, document_type, file_name, file_path, file_size, mime_type, description, uploaded_by, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: billing_status_logs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.billing_status_logs (id, billing_batch_id, status, notes, user_id, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache (key, value, expiration) FROM stdin;
laravel-cache-system_metrics_alt	a:8:{s:3:"cpu";a:5:{s:5:"usage";d:19.58;s:5:"cores";i:1;s:7:"threads";i:2;s:12:"load_average";a:3:{i:0;d:0.19580078125;i:1;d:0.17138671875;i:2;d:0.1103515625;}s:6:"status";s:4:"good";}s:6:"memory";a:9:{s:5:"total";i:1073741824;s:15:"total_formatted";s:4:"1 GB";s:4:"used";i:100663296;s:14:"used_formatted";s:5:"96 MB";s:4:"free";i:973078528;s:14:"free_formatted";s:6:"928 MB";s:10:"percentage";d:9.38;s:6:"status";s:4:"good";s:4:"swap";a:7:{s:5:"total";i:0;s:15:"total_formatted";s:3:"0 B";s:4:"used";i:0;s:14:"used_formatted";s:3:"0 B";s:4:"free";i:0;s:14:"free_formatted";s:3:"0 B";s:10:"percentage";i:0;}}s:4:"disk";a:1:{i:0;a:10:{s:5:"mount";s:21:"Application Directory";s:4:"path";s:41:"/www/wwwroot/proyek.cloudnexify.com/mitra";s:5:"total";d:102888095744;s:15:"total_formatted";s:8:"95.82 GB";s:4:"used";d:32975224832;s:14:"used_formatted";s:8:"30.71 GB";s:4:"free";d:69912870912;s:14:"free_formatted";s:8:"65.11 GB";s:10:"percentage";d:32.05;s:6:"status";s:4:"good";}}s:10:"php_memory";a:8:{s:5:"limit";i:134217728;s:15:"limit_formatted";s:6:"128 MB";s:7:"current";i:25165824;s:17:"current_formatted";s:5:"24 MB";s:4:"peak";i:25165824;s:14:"peak_formatted";s:5:"24 MB";s:10:"percentage";d:18.75;s:6:"status";s:4:"good";}s:8:"database";a:8:{s:4:"size";i:11776483;s:14:"size_formatted";s:8:"11.23 MB";s:6:"tables";i:40;s:18:"active_connections";i:15;s:15:"max_connections";i:100;s:21:"connection_percentage";d:15;s:4:"type";s:10:"PostgreSQL";s:6:"status";s:4:"good";}s:5:"cache";a:2:{s:6:"driver";s:8:"database";s:6:"status";s:6:"Active";}s:6:"uptime";a:6:{s:4:"days";d:6;s:5:"hours";d:14;s:7:"minutes";d:14;s:9:"formatted";s:16:"6 days, 14 hours";s:9:"boot_time";s:38:"Not available (restricted environment)";s:10:"app_uptime";s:16:"6 days, 14 hours";}s:11:"system_info";a:10:{s:11:"php_version";s:6:"8.2.28";s:15:"laravel_version";s:7:"12.25.0";s:15:"server_software";s:12:"nginx/1.27.5";s:2:"os";s:5:"Linux";s:8:"hostname";s:14:"host1749356433";s:8:"timezone";s:12:"Asia/Jakarta";s:12:"current_time";s:19:"2025-08-31 11:07:11";s:11:"environment";s:10:"production";s:10:"debug_mode";s:7:"Enabled";s:12:"restrictions";s:19:"open_basedir active";}}	1756613236
\.


--
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- Data for Name: cashflow_categories; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cashflow_categories (id, name, type, code, description, is_active, is_system, created_at, updated_at, "group", sort_order) FROM stdin;
1	Penagihan Proyek	income	INC_PROJECT_BILLING	Pemasukan dari penagihan proyek konstruksi telekomunikasi	t	t	2025-08-29 05:34:16	2025-08-29 05:43:46	proyek	10
2	Penagihan Batch	income	INC_BATCH_BILLING	Pemasukan dari penagihan batch	t	t	2025-08-29 05:34:16	2025-08-29 05:43:46	proyek	11
3	Penerimaan Pinjaman/Hutang	income	INC_LOAN_RECEIPT	Penerimaan modal dari pinjaman bank atau pihak ketiga	t	f	2025-08-29 05:34:16	2025-08-29 05:43:46	hutang_modal	20
4	Modal Investor	income	INC_INVESTOR_CAPITAL	Penerimaan modal dari investor atau pemegang saham	t	f	2025-08-29 05:34:16	2025-08-29 05:43:46	hutang_modal	21
5	Modal Awal/Tambahan	income	INC_INITIAL_CAPITAL	Modal awal atau tambahan modal dari pemilik	t	f	2025-08-29 05:34:16	2025-08-29 05:43:46	hutang_modal	22
6	Pembayaran Piutang	income	INC_RECEIVABLE_PAYMENT	Penerimaan pembayaran dari piutang usaha	t	f	2025-08-29 05:34:16	2025-08-29 05:43:46	piutang_tagihan	30
7	Pengembalian Pinjaman	income	INC_LOAN_RETURN	Penerimaan dari pengembalian pinjaman yang diberikan	t	f	2025-08-29 05:34:16	2025-08-29 05:43:46	piutang_tagihan	31
8	Penjualan Aset	income	INC_ASSET_SALE	Pendapatan dari penjualan aset tetap atau inventaris	t	f	2025-08-29 05:34:16	2025-08-29 05:43:46	pendapatan_lain	40
9	Sewa/Rental	income	INC_RENTAL	Pendapatan dari penyewaan aset atau properti	t	f	2025-08-29 05:34:16	2025-08-29 05:43:46	pendapatan_lain	41
10	Komisi/Fee	income	INC_COMMISSION	Pendapatan dari komisi atau fee jasa	t	f	2025-08-29 05:34:16	2025-08-29 05:43:46	pendapatan_lain	42
11	Dividen	income	INC_DIVIDEND	Penerimaan dividen dari investasi	t	f	2025-08-29 05:34:16	2025-08-29 05:43:46	pendapatan_lain	43
12	Bunga Bank	income	INC_BANK_INTEREST	Pendapatan bunga dari rekening bank	t	f	2025-08-29 05:34:16	2025-08-29 05:43:46	pendapatan_lain	44
13	Bunga Deposito	income	INC_DEPOSIT_INTEREST	Pendapatan bunga dari deposito	t	f	2025-08-29 05:34:16	2025-08-29 05:43:46	pendapatan_lain	45
14	Cashback/Diskon	income	INC_CASHBACK	Penerimaan cashback atau diskon pembelian	t	f	2025-08-29 05:34:16	2025-08-29 05:43:46	pendapatan_lain	46
15	Klaim Asuransi	income	INC_INSURANCE_CLAIM	Penerimaan klaim asuransi	t	f	2025-08-29 05:34:16	2025-08-29 05:43:46	pendapatan_lain	47
16	Hibah/Bantuan	income	INC_GRANT	Penerimaan hibah atau bantuan	t	f	2025-08-29 05:34:16	2025-08-29 05:43:46	pendapatan_lain	48
17	Pendapatan Lain-lain	income	INC_OTHER	Pendapatan dari sumber lain di luar kategori di atas	t	f	2025-08-29 05:34:16	2025-08-29 05:43:46	pendapatan_lain	49
18	Pengeluaran Proyek	expense	EXP_PROJECT	Pengeluaran untuk keperluan proyek konstruksi	t	t	2025-08-29 05:34:16	2025-08-29 05:43:46	proyek	100
19	Material & Peralatan Proyek	expense	EXP_PROJECT_MATERIAL	Pembelian material dan peralatan untuk proyek	t	f	2025-08-29 05:34:16	2025-08-29 05:43:46	proyek	101
20	Pembayaran Hutang Pokok	expense	EXP_DEBT_PRINCIPAL	Pembayaran cicilan hutang pokok	t	f	2025-08-29 05:34:16	2025-08-29 05:43:46	hutang_pinjaman	110
21	Bunga Pinjaman	expense	EXP_LOAN_INTEREST	Pembayaran bunga pinjaman	t	f	2025-08-29 05:34:16	2025-08-29 05:43:46	hutang_pinjaman	111
22	Denda/Penalty	expense	EXP_PENALTY	Pembayaran denda keterlambatan atau penalty	t	f	2025-08-29 05:34:16	2025-08-29 05:43:46	hutang_pinjaman	112
23	Pemberian Pinjaman	expense	EXP_LOAN_GIVEN	Pemberian pinjaman kepada pihak lain	t	f	2025-08-29 05:34:16	2025-08-29 05:43:46	hutang_pinjaman	113
24	Gaji dan Tunjangan	expense	EXP_SALARY	Pengeluaran untuk gaji karyawan dan tunjangan	t	f	2025-08-29 05:34:16	2025-08-29 05:43:46	operasional	120
25	Sewa Kantor/Gudang	expense	EXP_RENT	Pembayaran sewa kantor atau gudang	t	f	2025-08-29 05:34:16	2025-08-29 05:43:46	operasional	121
26	Listrik, Air, Internet	expense	EXP_UTILITIES	Pembayaran utilitas (listrik, air, internet, telepon)	t	f	2025-08-29 05:34:16	2025-08-29 05:43:46	operasional	122
27	Transportasi	expense	EXP_TRANSPORT	Biaya transportasi dan perjalanan dinas	t	f	2025-08-29 05:34:16	2025-08-29 05:43:46	operasional	123
28	Peralatan dan Supplies Kantor	expense	EXP_OFFICE_SUPPLIES	Pembelian peralatan dan supplies kantor	t	f	2025-08-29 05:34:16	2025-08-29 05:43:46	operasional	124
29	Maintenance/Perbaikan	expense	EXP_MAINTENANCE	Biaya perawatan dan perbaikan	t	f	2025-08-29 05:34:16	2025-08-29 05:43:46	operasional	125
30	Asuransi	expense	EXP_INSURANCE	Pembayaran premi asuransi	t	f	2025-08-29 05:34:16	2025-08-29 05:43:46	operasional	126
31	Biaya Operasional Lainnya	expense	EXP_OPERATIONAL	Biaya operasional kantor dan administrasi lainnya	t	f	2025-08-29 05:34:16	2025-08-29 05:43:46	operasional	129
32	Pembelian Aset	expense	EXP_ASSET_PURCHASE	Pembelian aset tetap atau inventaris	t	f	2025-08-29 05:34:16	2025-08-29 05:43:46	aset_investasi	130
33	Investasi	expense	EXP_INVESTMENT	Pengeluaran untuk investasi	t	f	2025-08-29 05:34:16	2025-08-29 05:43:46	aset_investasi	131
34	Pajak dan Retribusi	expense	EXP_TAX	Pembayaran pajak dan retribusi	t	f	2025-08-29 05:34:16	2025-08-29 05:43:46	pengeluaran_lain	140
35	Marketing/Promosi	expense	EXP_MARKETING	Biaya marketing dan promosi	t	f	2025-08-29 05:34:16	2025-08-29 05:43:46	pengeluaran_lain	141
36	Administrasi Bank	expense	EXP_BANK_ADMIN	Biaya administrasi bank	t	f	2025-08-29 05:34:16	2025-08-29 05:43:46	pengeluaran_lain	142
37	Legal/Notaris	expense	EXP_LEGAL	Biaya legal dan notaris	t	f	2025-08-29 05:34:16	2025-08-29 05:43:46	pengeluaran_lain	143
38	Konsultan	expense	EXP_CONSULTANT	Biaya konsultan	t	f	2025-08-29 05:34:16	2025-08-29 05:43:46	pengeluaran_lain	144
39	Entertainment	expense	EXP_ENTERTAINMENT	Biaya entertainment klien	t	f	2025-08-29 05:34:16	2025-08-29 05:43:46	pengeluaran_lain	145
40	CSR/Donasi	expense	EXP_DONATION	Pengeluaran CSR atau donasi	t	f	2025-08-29 05:34:16	2025-08-29 05:43:46	pengeluaran_lain	146
41	Pengeluaran Lain-lain	expense	EXP_OTHER	Pengeluaran lain-lain yang tidak masuk kategori di atas	t	f	2025-08-29 05:34:16	2025-08-29 05:43:46	pengeluaran_lain	149
42	Pendapatan Proyek	income	INC_PROJECT	Pendapatan dari proyek	t	t	2025-08-29 06:20:14	2025-08-29 06:20:14	project	1
43	Penjualan Produk	income	INC_SALES	Pendapatan dari penjualan produk	t	f	2025-08-29 06:38:19	2025-08-29 06:38:19	operational	10
44	Jasa Konsultasi	income	INC_CONSULTING	Pendapatan dari jasa konsultasi	t	f	2025-08-29 06:38:19	2025-08-29 06:38:19	operational	11
45	Penerimaan Piutang	income	INC_RECEIVABLE	Penerimaan pembayaran piutang	t	f	2025-08-29 06:38:19	2025-08-29 06:38:19	debt_receivable	20
46	Pinjaman Diterima	income	INC_LOAN_RECEIVED	Penerimaan pinjaman/hutang	t	f	2025-08-29 06:38:19	2025-08-29 06:38:19	debt_receivable	21
47	Modal Disetor	income	INC_CAPITAL	Setoran modal dari pemilik	t	f	2025-08-29 06:38:19	2025-08-29 06:38:19	investment	30
48	Investasi Masuk	income	INC_INVESTMENT	Penerimaan investasi	t	f	2025-08-29 06:38:19	2025-08-29 06:38:19	investment	31
49	Pengembalian Dana	income	INC_REFUND	Pengembalian dana/refund	t	f	2025-08-29 06:38:19	2025-08-29 06:38:19	other	41
50	Upah Proyek	expense	EXP_PROJECT_LABOR	Upah tenaga kerja proyek	t	f	2025-08-29 06:38:19	2025-08-29 06:38:19	project	52
51	Transportasi Proyek	expense	EXP_PROJECT_TRANSPORT	Biaya transportasi proyek	t	f	2025-08-29 06:38:19	2025-08-29 06:38:19	project	53
52	Internet & Telepon	expense	EXP_COMMUNICATION	Biaya internet dan telepon	t	f	2025-08-29 06:38:19	2025-08-29 06:38:19	operational	63
53	ATK & Supplies	expense	EXP_SUPPLIES	Alat tulis kantor dan supplies	t	f	2025-08-29 06:38:19	2025-08-29 06:38:19	operational	64
54	Pembayaran Hutang	expense	EXP_DEBT_PAYMENT	Pembayaran hutang/pinjaman	t	f	2025-08-29 06:38:19	2025-08-29 06:38:19	debt_receivable	70
55	Bunga Pinjaman	expense	EXP_INTEREST	Pembayaran bunga pinjaman	t	f	2025-08-29 06:38:19	2025-08-29 06:38:19	debt_receivable	71
56	Pajak PPh	expense	EXP_TAX_PPH	Pembayaran pajak PPh	t	f	2025-08-29 06:38:19	2025-08-29 06:38:19	tax	80
57	Pajak PPN	expense	EXP_TAX_PPN	Pembayaran pajak PPN	t	f	2025-08-29 06:38:19	2025-08-29 06:38:19	tax	81
58	Pajak Lainnya	expense	EXP_TAX_OTHER	Pembayaran pajak lainnya	t	f	2025-08-29 06:38:19	2025-08-29 06:38:19	tax	82
59	Pembelian Aset	expense	EXP_ASSET	Pembelian aset tetap	t	f	2025-08-29 06:38:19	2025-08-29 06:38:19	investment	90
60	Penarikan Modal	expense	EXP_CAPITAL_WITHDRAWAL	Penarikan modal oleh pemilik	t	f	2025-08-29 06:38:19	2025-08-29 06:38:19	investment	92
61	Biaya Bank	expense	EXP_BANK_FEE	Biaya administrasi bank	t	f	2025-08-29 06:38:19	2025-08-29 06:38:19	other	100
62	Gaji Karyawan	expense	SALARY_EXPENSE	Pengeluaran untuk gaji karyawan	t	f	2025-08-29 14:19:41	2025-08-29 14:19:41	\N	0
\.


--
-- Data for Name: cashflow_entries; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cashflow_entries (id, reference_type, reference_id, project_id, category_id, transaction_date, description, amount, type, payment_method, account_code, notes, created_by, status, confirmed_at, confirmed_by, created_at, updated_at) FROM stdin;
1	expense	1	29	18	2025-08-14	Kompensasi gagal kerja	200000.00	expense	bank_transfer	\N	\N	1	confirmed	2025-08-29 06:20:14	1	2025-08-29 06:20:14	2025-08-29 06:20:14
2	expense	2	29	18	2025-08-16	DP MANDORAN KE WAHYU DKK TIM MALAM	1000000.00	expense	bank_transfer	\N	\N	1	confirmed	2025-08-29 06:20:14	1	2025-08-29 06:20:14	2025-08-29 06:20:14
3	expense	3	30	18	2025-08-26	BENSIN APV	200000.00	expense	bank_transfer	\N	\N	1	confirmed	2025-08-29 06:20:14	1	2025-08-29 06:20:14	2025-08-29 06:20:14
4	expense	4	30	18	2025-08-26	BENSIN GRANMAX	200000.00	expense	bank_transfer	\N	\N	5	confirmed	2025-08-29 06:20:14	1	2025-08-29 06:20:14	2025-08-29 06:20:14
5	expense	5	30	18	2025-08-26	ETOL	100000.00	expense	bank_transfer	\N	\N	5	confirmed	2025-08-29 06:20:14	1	2025-08-29 06:20:14	2025-08-29 06:20:14
6	expense	6	30	18	2025-08-26	UANG AIR MINUM	50000.00	expense	bank_transfer	\N	\N	5	confirmed	2025-08-29 06:20:14	1	2025-08-29 06:20:14	2025-08-29 06:20:14
7	expense	7	30	18	2025-08-26	BENSIN ROKIM	100000.00	expense	bank_transfer	\N	\N	5	confirmed	2025-08-29 06:20:14	1	2025-08-29 06:20:14	2025-08-29 06:20:14
8	expense	8	31	18	2025-08-27	pipa pvc	50000.00	expense	bank_transfer	\N	\N	5	confirmed	2025-08-29 06:20:14	1	2025-08-29 06:20:14	2025-08-29 06:20:14
9	expense	10	28	18	2025-08-27	DP KE 1 FERY	20000000.00	expense	bank_transfer	\N	\N	1	confirmed	2025-08-29 06:20:14	1	2025-08-29 06:20:14	2025-08-29 06:20:14
10	expense	11	30	18	2025-08-28	bensin apv dan granmax	400000.00	expense	bank_transfer	\N	\N	5	confirmed	2025-08-29 06:20:14	1	2025-08-29 06:20:14	2025-08-29 06:20:14
11	expense	12	30	18	2025-08-28	etol	100000.00	expense	bank_transfer	\N	\N	5	confirmed	2025-08-29 06:20:14	1	2025-08-29 06:20:14	2025-08-29 06:20:14
12	expense	13	30	18	2025-08-28	bensin motor	50000.00	expense	bank_transfer	\N	\N	5	confirmed	2025-08-29 06:20:14	1	2025-08-29 06:20:14	2025-08-29 06:20:14
13	expense	14	30	18	2025-08-28	bensin ocim	150000.00	expense	bank_transfer	\N	\N	5	confirmed	2025-08-29 06:20:14	1	2025-08-29 06:20:14	2025-08-29 06:20:14
14	expense	15	31	18	2025-08-27	tali rafia	17000.00	expense	bank_transfer	\N	\N	5	confirmed	2025-08-29 06:20:14	1	2025-08-29 06:20:14	2025-08-29 06:20:14
15	expense	16	31	18	2025-08-27	beli air	50000.00	expense	bank_transfer	\N	\N	5	confirmed	2025-08-29 06:20:14	1	2025-08-29 06:20:14	2025-08-29 06:20:14
16	expense	17	31	18	2025-08-27	klem	35000.00	expense	bank_transfer	\N	\N	5	confirmed	2025-08-29 06:20:14	1	2025-08-29 06:20:14	2025-08-29 06:20:14
17	billing	1	29	42	2025-08-26	Pembayaran invoice 001/SK-08/2025	1000000.00	income	bank_transfer	\N	 | Dibatalkan karena tagihan dihapus pada 2025-08-29 06:48:26	1	cancelled	2025-08-29 06:20:14	1	2025-08-29 06:20:14	2025-08-29 06:48:26
18	manual	\N	\N	47	2025-08-13	Uang Kas	200000.00	income	bank_transfer	\N	\N	1	confirmed	2025-08-29 06:49:43	1	2025-08-29 06:49:43	2025-08-29 06:51:36
19	billing	2	29	1	2025-08-29	Pembayaran penagihan proyek: KAMPUNG MALANG (Termin 1 dari 4)	1000000.00	income	bank_transfer	\N	Auto-generated dari penagihan #001/SK-08/2025 | Dibatalkan karena tagihan dihapus pada 2025-08-29 06:53:22	1	cancelled	2025-08-29 06:52:44	1	2025-08-29 06:52:44	2025-08-29 06:53:22
20	billing	3	29	1	2025-08-29	Pembayaran penagihan proyek: KAMPUNG MALANG (Termin 1 dari 4)	1000000.00	income	bank_transfer	\N	Auto-generated dari penagihan #001/INV-08/2025 | Dibatalkan karena tagihan dihapus pada 2025-08-29 06:55:43	1	cancelled	2025-08-29 06:55:29	1	2025-08-29 06:55:29	2025-08-29 06:55:43
21	billing	4	29	1	2025-08-15	Pembayaran penagihan proyek: KAMPUNG MALANG (Termin 1 dari 4)	1000000.00	income	bank_transfer	\N	Auto-generated dari penagihan #001/INV-08/2025	1	confirmed	2025-08-15 00:00:00	1	2025-08-29 11:18:14	2025-08-29 11:18:14
22	manual	\N	\N	5	2025-08-25	dari 4400040404	1600000.00	income	cash	\N	\N	1	confirmed	2025-08-29 11:20:07	1	2025-08-29 11:20:07	2025-08-29 11:21:00
23	manual	\N	\N	3	2025-08-26	dari bondan	20000000.00	income	bank_transfer	\N	\N	1	confirmed	2025-08-29 11:21:41	1	2025-08-29 11:21:41	2025-08-29 11:21:41
24	manual	\N	\N	5	2025-08-28	dari 4400040404	1000000.00	income	cash	\N	\N	1	confirmed	2025-08-29 11:32:08	1	2025-08-29 11:32:08	2025-08-29 11:32:08
25	expense	18	34	18	2025-08-28	Pengeluaran: pembayaran gamas, teknisi wahyu dkk	900000.00	expense	cash	\N	Auto-generated dari pengeluaran proyek | Kategori: labor	5	confirmed	2025-08-29 13:05:19	5	2025-08-29 13:05:19	2025-08-29 13:05:19
26	expense	1	\N	62	2025-08-29	Pembayaran gaji SUTIKNO periode 11/08/2025 - 24/08/2025	1285000.00	expense	\N	\N	Rilis gaji dengan kode: SR20250829U2QA	5	confirmed	2025-08-29 14:19:41	5	2025-08-29 14:19:41	2025-08-29 14:19:41
27	expense	2	\N	62	2025-08-29	Pembayaran gaji BASUKI RAHMAWANTO periode 11/08/2025 - 25/08/2025	1775000.00	expense	\N	\N	Rilis gaji dengan kode: SR20250829ODUG	5	confirmed	2025-08-29 14:23:32	5	2025-08-29 14:23:32	2025-08-29 14:23:32
28	expense	3	\N	62	2025-08-29	Pembayaran gaji TEGUH DWI ARIFIANTO periode 11/08/2025 - 24/08/2025	1535000.00	expense	\N	\N	Rilis gaji dengan kode: SR20250829QVER	5	confirmed	2025-08-29 14:35:40	5	2025-08-29 14:35:40	2025-08-29 14:35:40
29	manual	\N	\N	47	2025-08-25	dari 4400040404	4600000.00	income	bank_transfer	\N	\N	1	confirmed	2025-08-30 05:05:30	1	2025-08-30 05:05:30	2025-08-30 05:05:30
30	manual	\N	\N	47	2025-08-29	dari 4400040404 untuk tf ke pras beli material	200000.00	income	bank_transfer	\N	\N	1	confirmed	2025-08-30 05:07:15	1	2025-08-30 05:07:15	2025-08-30 05:07:15
\.


--
-- Data for Name: companies; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.companies (id, name, address, phone, email, contact_person, created_at, updated_at, is_active) FROM stdin;
\.


--
-- Data for Name: daily_salaries; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.daily_salaries (id, employee_id, work_date, amount, hours_worked, overtime_hours, overtime_rate, notes, status, created_by, created_at, updated_at, basic_salary, meal_allowance, attendance_bonus, phone_allowance, transport_allowance, attendance_status, check_in_time, check_out_time, deductions, total_amount, deleted_at, overtime_amount, salary_release_id) FROM stdin;
1	10	2025-08-11	150000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 13:55:59	2025-08-29 13:55:59	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
2	10	2025-08-12	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 13:57:11	2025-08-29 13:57:11	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
3	10	2025-08-13	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 13:57:15	2025-08-29 13:57:15	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
4	10	2025-08-14	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 13:57:20	2025-08-29 13:57:20	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
5	10	2025-08-15	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 13:57:24	2025-08-29 13:57:24	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
6	10	2025-08-16	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 13:57:28	2025-08-29 13:57:28	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
7	10	2025-08-17	0.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 13:57:52	2025-08-29 13:57:52	0.00	0.00	0.00	0.00	0.00	absent	\N	\N	0.00	0.00	\N	0.00	\N
9	10	2025-08-19	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 13:59:32	2025-08-29 13:59:32	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
10	10	2025-08-20	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 13:59:36	2025-08-29 13:59:36	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
12	10	2025-08-22	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 13:59:46	2025-08-29 13:59:46	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
13	10	2025-08-23	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 13:59:50	2025-08-29 13:59:50	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
14	10	2025-08-24	0.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 13:59:55	2025-08-29 13:59:55	0.00	0.00	0.00	0.00	0.00	absent	\N	\N	0.00	0.00	\N	0.00	\N
15	10	2025-08-25	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:00:00	2025-08-29 14:00:00	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
16	10	2025-08-26	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:00:04	2025-08-29 14:00:04	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
17	10	2025-08-27	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:00:08	2025-08-29 14:00:08	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
18	10	2025-08-28	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:00:12	2025-08-29 14:00:12	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
19	10	2025-08-29	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:00:16	2025-08-29 14:00:16	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
47	9	2025-08-21	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:05:21	2025-08-29 14:05:21	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
8	10	2025-08-18	135000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 13:59:28	2025-08-29 14:00:57	115000.00	0.00	20000.00	0.00	0.00	present	\N	\N	0.00	135000.00	\N	0.00	\N
20	4	2025-08-17	0.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:01:45	2025-08-29 14:01:45	0.00	0.00	0.00	0.00	0.00	absent	\N	\N	0.00	0.00	\N	0.00	\N
22	4	2025-08-19	0.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:01:58	2025-08-29 14:01:58	0.00	0.00	0.00	0.00	0.00	absent	\N	\N	0.00	0.00	\N	0.00	\N
23	4	2025-08-20	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:02:06	2025-08-29 14:02:06	115000.00	0.00	0.00	0.00	0.00	sick	\N	\N	65000.00	50000.00	\N	0.00	\N
24	4	2025-08-11	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:02:11	2025-08-29 14:02:11	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
25	4	2025-08-12	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:02:15	2025-08-29 14:02:15	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
26	4	2025-08-13	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:02:19	2025-08-29 14:02:19	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
27	4	2025-08-14	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:02:25	2025-08-29 14:02:25	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
28	4	2025-08-15	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:02:29	2025-08-29 14:02:29	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
29	4	2025-08-16	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:02:33	2025-08-29 14:02:33	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
31	4	2025-08-22	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:02:41	2025-08-29 14:02:41	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
32	4	2025-08-23	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:02:45	2025-08-29 14:02:45	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
33	4	2025-08-24	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:02:49	2025-08-29 14:02:49	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
34	4	2025-08-25	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:02:53	2025-08-29 14:02:53	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
35	4	2025-08-26	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:02:57	2025-08-29 14:02:57	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
36	4	2025-08-27	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:03:01	2025-08-29 14:03:01	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
37	4	2025-08-28	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:03:05	2025-08-29 14:03:05	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
38	4	2025-08-29	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:03:08	2025-08-29 14:03:08	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
21	4	2025-08-18	135000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:01:49	2025-08-29 14:03:21	115000.00	0.00	20000.00	0.00	0.00	present	\N	\N	0.00	135000.00	\N	0.00	\N
39	9	2025-08-22	0.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:03:45	2025-08-29 14:03:45	0.00	0.00	0.00	0.00	0.00	absent	\N	\N	0.00	0.00	\N	0.00	\N
40	9	2025-08-23	0.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:03:49	2025-08-29 14:03:49	0.00	0.00	0.00	0.00	0.00	absent	\N	\N	0.00	0.00	\N	0.00	\N
41	9	2025-08-24	0.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:03:56	2025-08-29 14:03:56	0.00	0.00	0.00	0.00	0.00	absent	\N	\N	0.00	0.00	\N	0.00	\N
42	9	2025-08-25	0.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:04:00	2025-08-29 14:04:00	0.00	0.00	0.00	0.00	0.00	absent	\N	\N	0.00	0.00	\N	0.00	\N
43	9	2025-08-26	0.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:04:24	2025-08-29 14:04:24	0.00	0.00	0.00	0.00	0.00	absent	\N	\N	0.00	0.00	\N	0.00	\N
44	9	2025-08-27	0.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:04:28	2025-08-29 14:04:28	0.00	0.00	0.00	0.00	0.00	absent	\N	\N	0.00	0.00	\N	0.00	\N
45	9	2025-08-28	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:04:59	2025-08-29 14:04:59	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
46	9	2025-08-29	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:05:04	2025-08-29 14:05:04	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
48	9	2025-08-19	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:05:24	2025-08-29 14:05:24	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
49	9	2025-08-20	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:05:29	2025-08-29 14:05:29	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
51	9	2025-08-17	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:05:37	2025-08-29 14:05:37	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
52	9	2025-08-16	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:05:41	2025-08-29 14:05:41	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
11	10	2025-08-21	200000.00	8.00	0.00	0.00	bon matrial	confirmed	5	2025-08-29 13:59:40	2025-08-29 14:24:27	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	200000.00	\N	50000.00	\N
30	4	2025-08-21	200000.00	8.00	0.00	0.00	bon matrial	confirmed	5	2025-08-29 14:02:37	2025-08-29 14:25:03	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	200000.00	\N	50000.00	\N
53	9	2025-08-15	200000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:05:45	2025-08-29 14:25:46	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	200000.00	\N	50000.00	\N
54	9	2025-08-11	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:05:49	2025-08-29 14:05:49	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
55	9	2025-08-12	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:05:54	2025-08-29 14:05:54	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
56	9	2025-08-13	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:05:59	2025-08-29 14:05:59	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
57	9	2025-08-14	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:06:03	2025-08-29 14:06:03	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
50	9	2025-08-18	135000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:05:33	2025-08-29 14:06:17	115000.00	0.00	20000.00	0.00	0.00	present	\N	\N	0.00	135000.00	\N	0.00	\N
58	6	2025-08-12	0.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:06:42	2025-08-29 14:06:42	0.00	0.00	0.00	0.00	0.00	absent	\N	\N	0.00	0.00	\N	0.00	\N
59	6	2025-08-13	0.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:06:47	2025-08-29 14:06:47	0.00	0.00	0.00	0.00	0.00	absent	\N	\N	0.00	0.00	\N	0.00	\N
60	6	2025-08-14	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:06:51	2025-08-29 14:06:55	115000.00	0.00	0.00	0.00	0.00	sick	\N	\N	65000.00	50000.00	\N	0.00	\N
61	6	2025-08-24	0.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:07:05	2025-08-29 14:07:05	0.00	0.00	0.00	0.00	0.00	absent	\N	\N	0.00	0.00	\N	0.00	\N
62	6	2025-08-25	0.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:07:09	2025-08-29 14:07:09	0.00	0.00	0.00	0.00	0.00	absent	\N	\N	0.00	0.00	\N	0.00	\N
63	6	2025-08-11	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:07:13	2025-08-29 14:07:13	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
64	6	2025-08-15	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:07:18	2025-08-29 14:07:18	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
65	6	2025-08-16	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:07:23	2025-08-29 14:07:23	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
66	6	2025-08-17	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:07:27	2025-08-29 14:07:27	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
68	6	2025-08-19	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:07:35	2025-08-29 14:07:35	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
69	6	2025-08-20	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:07:39	2025-08-29 14:07:39	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
71	6	2025-08-22	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:07:47	2025-08-29 14:07:47	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
72	6	2025-08-23	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:07:51	2025-08-29 14:07:51	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
73	6	2025-08-26	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:07:55	2025-08-29 14:07:55	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
74	6	2025-08-27	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:07:59	2025-08-29 14:07:59	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
75	6	2025-08-29	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:08:03	2025-08-29 14:08:03	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
76	6	2025-08-28	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:08:07	2025-08-29 14:08:07	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
77	8	2025-08-19	0.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:08:41	2025-08-29 14:08:41	0.00	0.00	0.00	0.00	0.00	absent	\N	\N	0.00	0.00	\N	0.00	\N
78	8	2025-08-24	0.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:08:46	2025-08-29 14:08:46	0.00	0.00	0.00	0.00	0.00	absent	\N	\N	0.00	0.00	\N	0.00	\N
79	8	2025-08-11	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:08:50	2025-08-29 14:08:50	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
80	8	2025-08-12	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:08:55	2025-08-29 14:08:55	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
81	8	2025-08-13	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:08:59	2025-08-29 14:08:59	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
82	8	2025-08-14	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:09:03	2025-08-29 14:09:03	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
83	8	2025-08-15	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:09:06	2025-08-29 14:09:06	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
84	8	2025-08-16	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:09:10	2025-08-29 14:09:10	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
85	8	2025-08-17	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:09:14	2025-08-29 14:09:14	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
87	8	2025-08-20	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:09:23	2025-08-29 14:09:23	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
89	8	2025-08-22	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:09:31	2025-08-29 14:09:31	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
90	8	2025-08-23	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:09:35	2025-08-29 14:09:35	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
91	8	2025-08-25	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:09:40	2025-08-29 14:09:40	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
92	8	2025-08-26	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:09:45	2025-08-29 14:09:45	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
93	8	2025-08-27	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:09:49	2025-08-29 14:09:49	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
94	8	2025-08-28	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:09:54	2025-08-29 14:09:54	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
95	8	2025-08-29	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:10:00	2025-08-29 14:10:00	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
86	8	2025-08-18	135000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:09:19	2025-08-29 14:10:16	115000.00	0.00	20000.00	0.00	0.00	present	\N	\N	0.00	135000.00	\N	0.00	\N
67	6	2025-08-18	135000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:07:31	2025-08-29 14:11:05	115000.00	0.00	20000.00	0.00	0.00	present	\N	\N	0.00	135000.00	\N	0.00	\N
70	6	2025-08-21	200000.00	8.00	0.00	0.00	bon mtrial	confirmed	5	2025-08-29 14:07:43	2025-08-29 14:26:13	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	200000.00	\N	50000.00	\N
88	8	2025-08-21	200000.00	8.00	0.00	0.00	bon matrial	confirmed	5	2025-08-29 14:09:27	2025-08-29 14:27:06	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	200000.00	\N	50000.00	\N
96	3	2025-08-17	0.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:12:03	2025-08-29 14:23:27	0.00	0.00	0.00	0.00	0.00	absent	\N	\N	0.00	0.00	\N	0.00	2
110	3	2025-08-29	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:13:48	2025-08-29 14:13:48	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
111	3	2025-08-28	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:13:55	2025-08-29 14:13:55	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
112	3	2025-08-27	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:14:01	2025-08-29 14:14:01	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
113	3	2025-08-26	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:14:05	2025-08-29 14:14:05	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
145	7	2025-08-21	200000.00	8.00	0.00	0.00	bon matrial	confirmed	5	2025-08-29 14:21:02	2025-08-29 14:35:34	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	200000.00	\N	50000.00	3
129	5	2025-08-25	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:17:15	2025-08-29 14:17:15	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
130	5	2025-08-26	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:17:19	2025-08-29 14:17:19	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
131	5	2025-08-27	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:17:24	2025-08-29 14:17:24	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
132	5	2025-08-28	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:17:28	2025-08-29 14:17:28	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
133	5	2025-08-29	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:17:32	2025-08-29 14:17:32	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
115	5	2025-08-19	0.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:16:12	2025-08-29 14:19:33	0.00	0.00	0.00	0.00	0.00	absent	\N	\N	0.00	0.00	\N	0.00	1
116	5	2025-08-20	0.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:16:17	2025-08-29 14:19:33	0.00	0.00	0.00	0.00	0.00	absent	\N	\N	0.00	0.00	\N	0.00	1
117	5	2025-08-11	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:16:21	2025-08-29 14:19:33	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	1
118	5	2025-08-12	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:16:26	2025-08-29 14:19:33	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	1
119	5	2025-08-13	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:16:30	2025-08-29 14:19:33	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	1
120	5	2025-08-14	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:16:34	2025-08-29 14:19:33	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	1
121	5	2025-08-15	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:16:41	2025-08-29 14:19:33	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	1
122	5	2025-08-16	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:16:45	2025-08-29 14:19:33	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	1
123	5	2025-08-17	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:16:49	2025-08-29 14:19:33	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	1
125	5	2025-08-21	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:16:57	2025-08-29 14:19:34	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	1
126	5	2025-08-22	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:17:01	2025-08-29 14:19:34	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	1
127	5	2025-08-23	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:17:06	2025-08-29 14:19:34	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	1
128	5	2025-08-24	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:17:10	2025-08-29 14:19:34	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	1
124	5	2025-08-18	135000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:16:53	2025-08-29 14:19:34	115000.00	0.00	20000.00	0.00	0.00	present	\N	\N	0.00	135000.00	\N	0.00	1
148	7	2025-08-25	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:21:14	2025-08-29 14:21:14	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
149	7	2025-08-26	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:21:18	2025-08-29 14:21:18	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
150	7	2025-08-27	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:21:23	2025-08-29 14:21:23	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
151	7	2025-08-28	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:21:28	2025-08-29 14:21:28	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
152	7	2025-08-29	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:21:32	2025-08-29 14:21:32	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	\N
97	3	2025-08-23	0.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:12:08	2025-08-29 14:23:27	0.00	0.00	0.00	0.00	0.00	absent	\N	\N	0.00	0.00	\N	0.00	2
98	3	2025-08-24	0.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:12:13	2025-08-29 14:23:27	0.00	0.00	0.00	0.00	0.00	absent	\N	\N	0.00	0.00	\N	0.00	2
99	3	2025-08-11	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:12:19	2025-08-29 14:23:27	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	2
135	7	2025-08-24	0.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:20:20	2025-08-29 14:35:34	0.00	0.00	0.00	0.00	0.00	absent	\N	\N	0.00	0.00	\N	0.00	3
134	7	2025-08-19	0.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:20:15	2025-08-29 14:35:34	0.00	0.00	0.00	0.00	0.00	absent	\N	\N	0.00	0.00	\N	0.00	3
136	7	2025-08-11	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:20:25	2025-08-29 14:35:34	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	3
137	7	2025-08-12	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:20:29	2025-08-29 14:35:34	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	3
138	7	2025-08-13	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:20:33	2025-08-29 14:35:34	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	3
100	3	2025-08-12	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:12:25	2025-08-29 14:23:27	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	2
101	3	2025-08-13	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:12:31	2025-08-29 14:23:27	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	2
102	3	2025-08-14	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:13:09	2025-08-29 14:23:27	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	2
104	3	2025-08-16	130000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:13:19	2025-08-29 14:23:27	115000.00	10000.00	0.00	5000.00	0.00	present	\N	\N	0.00	130000.00	\N	0.00	2
103	3	2025-08-15	200000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:13:15	2025-08-29 14:23:27	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	200000.00	\N	50000.00	2
106	3	2025-08-19	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:13:28	2025-08-29 14:23:27	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	2
109	3	2025-08-22	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:13:43	2025-08-29 14:23:27	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	2
114	3	2025-08-25	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:14:09	2025-08-29 14:23:27	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	2
105	3	2025-08-18	135000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:13:24	2025-08-29 14:23:27	115000.00	0.00	20000.00	0.00	0.00	present	\N	\N	0.00	135000.00	\N	0.00	2
107	3	2025-08-20	130000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:13:33	2025-08-29 14:23:27	115000.00	10000.00	0.00	5000.00	0.00	present	\N	\N	0.00	130000.00	\N	0.00	2
108	3	2025-08-21	130000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:13:37	2025-08-29 14:23:27	115000.00	10000.00	0.00	5000.00	0.00	present	\N	\N	0.00	130000.00	\N	0.00	2
139	7	2025-08-14	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:20:37	2025-08-29 14:35:34	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	3
140	7	2025-08-15	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:20:41	2025-08-29 14:35:34	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	3
141	7	2025-08-16	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:20:45	2025-08-29 14:35:34	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	3
142	7	2025-08-17	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:20:49	2025-08-29 14:35:34	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	3
144	7	2025-08-20	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:20:58	2025-08-29 14:35:34	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	3
146	7	2025-08-22	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:21:06	2025-08-29 14:35:34	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	3
147	7	2025-08-23	115000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:21:10	2025-08-29 14:35:34	115000.00	10000.00	20000.00	5000.00	0.00	present	\N	\N	0.00	150000.00	\N	0.00	3
143	7	2025-08-18	135000.00	8.00	0.00	0.00	\N	confirmed	5	2025-08-29 14:20:53	2025-08-29 14:35:34	115000.00	0.00	20000.00	0.00	0.00	present	\N	\N	0.00	135000.00	\N	0.00	3
\.


--
-- Data for Name: employee_custom_off_days; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.employee_custom_off_days (id, employee_id, off_date, reason, period_month, period_year, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: employee_work_schedules; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.employee_work_schedules (id, employee_id, schedule_type, work_days_per_month, standard_off_days, effective_from, effective_until, is_active, notes, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: employees; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.employees (id, employee_code, name, "position", department, hire_date, phone, address, status, daily_rate, notes, created_at, updated_at, email, birth_date, gender, id_number, emergency_contact_name, emergency_contact_phone, emergency_contact_relation, employment_type, contract_end_date, bank_name, bank_account_number, bank_account_name, avatar, deleted_at) FROM stdin;
2	EMP2002	AUDREYA PUTRI M	ADMIN	OFFICE	2022-03-01	085784935091	\N	active	115000.00	\N	2025-08-25 13:58:29	2025-08-25 14:12:09	momo@cloudnexify.com	\N	\N	\N	\N	\N	\N	permanent	\N	\N	\N	\N	\N	\N
3	EMP2003	BASUKI RAHMAWANTO	TEKNISI	OPERASIONAL	2021-03-01	081227572037	\N	active	115000.00	\N	2025-08-25 14:00:34	2025-08-25 14:12:30	\N	\N	\N	\N	\N	\N	\N	freelance	\N	\N	\N	\N	\N	\N
10	EMP2010	EDI SUPARMAN	TEKNISI	OPERASIONAL	2025-03-01	083166547907	\N	active	115000.00	\N	2025-08-25 14:10:17	2025-08-25 14:12:45	\N	\N	\N	\N	\N	\N	\N	freelance	\N	\N	\N	\N	\N	\N
4	EMP2004	MOKHAMAD SYAIFUDIN ZUHRI	TEKNISI	OPERASIONAL	2021-03-01	085158103013	\N	active	115000.00	\N	2025-08-25 14:01:28	2025-08-25 14:13:29	\N	\N	\N	\N	\N	\N	\N	freelance	\N	\N	\N	\N	\N	\N
9	EMP2009	PRASETYO	TEKNISI	OPERASIONAL	2024-03-01	085168630370	\N	active	115000.00	\N	2025-08-25 14:09:21	2025-08-25 14:13:43	\N	\N	\N	\N	\N	\N	\N	freelance	\N	\N	\N	\N	\N	\N
6	EMP2006	SANTO	TEKNISI	OPERASIONAL	2021-03-01	082242536939	\N	active	115000.00	\N	2025-08-25 14:03:27	2025-08-25 14:13:58	\N	\N	\N	\N	\N	\N	\N	freelance	\N	\N	\N	\N	\N	\N
5	EMP2005	SUTIKNO	TEKNISI	OPERASIONAL	2021-03-01	085878004357	\N	active	115000.00	\N	2025-08-25 14:02:44	2025-08-25 14:14:13	\N	\N	\N	\N	\N	\N	\N	freelance	\N	\N	\N	\N	\N	\N
7	EMP2007	TEGUH DWI ARIFIANTO	TEKNISI	OPERASIONAL	2021-03-01	082243040922	\N	active	115000.00	\N	2025-08-25 14:06:05	2025-08-25 14:14:50	\N	\N	\N	\N	\N	\N	\N	freelance	\N	\N	\N	\N	\N	\N
8	EMP2008	WAHYU ADI TRI	TEKNISI	OPERASIONAL	2022-03-01	082144352088	\N	active	115000.00	\N	2025-08-25 14:07:01	2025-08-25 14:15:38	\N	\N	\N	\N	\N	\N	\N	freelance	\N	\N	\N	\N	\N	\N
1	EMP2001	M. ABDUL ROCHIM	PENGAWAS LAPANGAN	OFFICE	2021-03-01	082139733850	\N	active	160000.00	\N	2025-08-25 13:56:59	2025-08-25 14:16:21	\N	\N	\N	\N	\N	\N	\N	permanent	\N	\N	\N	\N	\N	\N
\.


--
-- Data for Name: expense_approvals; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.expense_approvals (id, expense_id, approver_id, level, status, notes, approved_at, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: expense_modification_approvals; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.expense_modification_approvals (id, expense_id, action_type, requested_by, original_data, proposed_data, reason, status, approved_by, approved_at, approval_notes, created_at, updated_at) FROM stdin;
1	17	edit	5	{"id":17,"project_id":31,"user_id":5,"description":"klem","amount":"35000.00","expense_date":"2025-08-26T17:00:00.000000Z","created_at":"2025-08-28T10:55:35.000000Z","updated_at":"2025-08-28T10:55:35.000000Z","category":"material","receipt_number":null,"vendor":null,"notes":null,"status":"approved"}	{"project_id":"30","description":"klem","category":"material","amount":"35000","expense_date":"2025-08-27","receipt_number":null,"vendor":null,"notes":null}	salah input proyek	pending	\N	\N	\N	2025-08-28 18:04:20	2025-08-28 18:04:20
\.


--
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.failed_jobs (id, uuid, connection, queue, payload, exception, failed_at) FROM stdin;
\.


--
-- Data for Name: import_logs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.import_logs (id, user_id, file_name, file_path, status, total_rows, successful_rows, failed_rows, error_message, failed_rows_details, completed_at, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: job_batches; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.job_batches (id, name, total_jobs, pending_jobs, failed_jobs, failed_job_ids, options, cancelled_at, created_at, finished_at) FROM stdin;
\.


--
-- Data for Name: jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.jobs (id, queue, payload, attempts, reserved_at, available_at, created_at) FROM stdin;
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	0001_01_01_000000_create_users_table	1
2	0001_01_01_000001_create_cache_table	1
3	0001_01_01_000002_create_jobs_table	1
4	2025_07_24_010356_create_roles_table	1
5	2025_07_24_010414_create_role_users_table	1
6	2025_07_24_010513_create_companies_table	1
7	2025_07_24_010529_create_projects_table	1
8	2025_07_24_010538_create_project_expenses_table	1
9	2025_07_24_010554_create_expense_approvals_table	1
10	2025_07_24_010555_create_expense_modification_approvals_table	1
11	2025_07_24_010609_create_project_activities_table	1
12	2025_07_24_010628_create_project_timelines_table	1
13	2025_07_24_010646_create_project_billings_table	1
14	2025_07_24_010647_add_termin_fields_to_project_billings_table	1
15	2025_07_24_010648_create_project_payment_schedules_table	1
16	2025_07_24_010649_add_parent_schedule_foreign_key	1
17	2025_07_24_010702_create_project_revenues_table	1
18	2025_07_24_010725_create_revenue_items_table	1
19	2025_07_24_010744_create_project_profit_analyses_table	1
20	2025_07_24_010757_create_import_logs_table	1
21	2025_07_24_041421_remove_company_id_from_projects_table	1
22	2025_07_24_041444_add_company_info_to_users_table	1
23	2025_07_24_044230_add_code_to_projects_table	1
24	2025_07_24_044325_update_projects_enum_values	1
25	2025_07_24_045120_add_project_value_fields	1
26	2025_07_24_074019_fix_project_value_fields_constraints	1
27	2025_07_24_090850_add_missing_columns_to_project_expenses_table	1
28	2025_07_24_090950_update_project_expenses_status_enum	1
29	2025_07_24_093843_create_project_documents_table	1
30	2025_07_24_105258_add_invoice_fields_to_project_billings_table	1
31	2025_07_24_152629_add_sp_number_to_project_billings_table	1
32	2025_07_24_152933_add_tax_invoice_number_to_project_billings_table	1
33	2025_07_24_153219_remove_due_date_from_project_billings_table	1
34	2025_07_25_002406_create_billing_batches_table	1
35	2025_07_25_002440_create_billing_status_logs_table	1
36	2025_07_25_002508_create_billing_documents_table	1
37	2025_07_25_002537_add_billing_batch_id_to_project_billings_table	1
38	2025_07_25_012855_add_client_type_to_projects_table	1
39	2025_07_25_015135_update_director_to_direktur_in_expense_approvals	1
40	2025_07_25_025800_add_client_type_to_billing_batches_table	1
41	2025_07_25_044631_add_billing_fields_to_projects_table	1
42	2025_07_25_100939_add_psb_to_project_types	1
43	2025_07_25_101004_create_project_locations_table	1
44	2025_07_25_102127_add_client_to_projects_table	1
45	2025_07_25_102156_create_project_clients_table	1
46	2025_07_26_075420_add_is_active_to_companies_table	1
47	2025_08_11_214317_create_cashflow_categories_table	1
48	2025_08_11_214325_create_cashflow_entries_table	1
49	2025_08_11_230401_create_employees_table	1
50	2025_08_11_230417_create_daily_salaries_table	1
51	2025_08_11_230430_create_salary_releases_table	1
52	2025_08_12_041820_enhance_employees_table	1
53	2025_08_12_050000_enhance_daily_salaries_with_salary_components	1
54	2025_08_12_105034_add_deleted_at_to_daily_salaries_table	1
55	2025_08_12_105035_add_deleted_at_to_employees_table	1
56	2025_08_12_105036_add_overtime_amount_to_daily_salaries_table	1
57	2025_08_12_105037_add_deleted_at_to_salary_releases_table	1
58	2025_08_13_104900_fix_daily_salaries_unique_constraint_for_soft_deletes	1
59	2025_08_13_182433_add_salary_release_id_to_daily_salaries_table	1
60	2025_08_13_182615_add_missing_fields_to_salary_releases_table	1
61	2025_08_14_081900_create_employee_work_schedules_table	1
62	2025_08_14_081901_create_employee_custom_off_days_table	1
63	2025_08_14_124122_create_settings_table	1
64	2025_08_28_000000_add_group_to_cashflow_categories_table	2
65	2025_08_29_000001_insert_default_roles	3
66	2025_08_29_000002_insert_default_settings	3
67	2025_08_29_000003_insert_default_cashflow_categories	3
68	2025_08_29_000004_insert_default_users	3
69	2025_01_29_000001_update_project_documents_for_storage_system	4
70	2025_01_29_000002_create_project_folders_table	4
71	2025_01_29_000003_create_sync_logs_table	4
\.


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.


--
-- Data for Name: project_activities; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.project_activities (id, project_id, user_id, activity_type, description, changes, created_at, updated_at) FROM stdin;
1	1	1	project_created	Proyek '3SBU-BBE-MD PT2 EXPAND-ODP-BBE-FAQ-16' telah dibuat	{"project_name":"3SBU-BBE-MD PT2 EXPAND-ODP-BBE-FAQ-16","project_code":"PRJ-2025-08-001","budget":"316282.00","status":"completed"}	2025-08-26 10:02:09	2025-08-26 10:02:09
2	2	1	project_created	Proyek '3SBU-BBE-MD PT2 EXPAND-ODP-BBE-FAP-28' telah dibuat	{"project_name":"3SBU-BBE-MD PT2 EXPAND-ODP-BBE-FAP-28","project_code":"PRJ-2025-08-002","budget":"316282.00","status":"completed"}	2025-08-26 10:02:40	2025-08-26 10:02:40
3	3	1	project_created	Proyek '3SBU-BBE-MD PT2 EXPAND-ODP-BBE-FAQ-03' telah dibuat	{"project_name":"3SBU-BBE-MD PT2 EXPAND-ODP-BBE-FAQ-03","project_code":"PRJ-2025-08-003","budget":"316282.00","status":"completed"}	2025-08-26 10:03:14	2025-08-26 10:03:14
4	4	1	project_created	Proyek '3SBU-BBE-MD PT2 EXPAND-ODP-BBE-FAQ-09' telah dibuat	{"project_name":"3SBU-BBE-MD PT2 EXPAND-ODP-BBE-FAQ-09","project_code":"PRJ-2025-08-004","budget":"316282.00","status":"completed"}	2025-08-26 10:03:55	2025-08-26 10:03:55
5	5	1	project_created	Proyek '3SBU-KRP-PT3-KEMLATEN GG MASJID NO.22' telah dibuat	{"project_name":"3SBU-KRP-PT3-KEMLATEN GG MASJID NO.22","project_code":"PRJ-2025-08-005","budget":"605840.00","status":"completed"}	2025-08-26 10:06:48	2025-08-26 10:06:48
6	6	1	project_created	Proyek '3SBU-LKI-PT3-APC-ALANA REGENCY GSI' telah dibuat	{"project_name":"3SBU-LKI-PT3-APC-ALANA REGENCY GSI","project_code":"PRJ-2025-08-006","budget":"18288140.00","status":"completed"}	2025-08-26 10:08:15	2025-08-26 10:08:15
7	7	1	project_created	Proyek '3SBU-GSK-PT3-RUSUNAWA USMAN SADAR' telah dibuat	{"project_name":"3SBU-GSK-PT3-RUSUNAWA USMAN SADAR","project_code":"PRJ-2025-08-007","budget":"1000000.00","status":"completed"}	2025-08-26 11:04:21	2025-08-26 11:04:21
8	8	1	project_created	Proyek '5SBU-GSK-PT3-JL. SUNAN GIRI GANG 13 E' telah dibuat	{"project_name":"5SBU-GSK-PT3-JL. SUNAN GIRI GANG 13 E","project_code":"PRJ-2025-08-008","budget":"1000000.00","status":"completed"}	2025-08-26 11:05:20	2025-08-26 11:05:20
9	9	1	project_created	Proyek '3SBU-DBS25-PT3-SDY-FB-KCP (1001840019)' telah dibuat	{"project_name":"3SBU-DBS25-PT3-SDY-FB-KCP (1001840019)","project_code":"PRJ-2025-08-009","budget":"500000.00","status":"completed"}	2025-08-26 11:06:18	2025-08-26 11:06:18
10	10	1	project_created	Proyek '5SBU-KRP-PT3-KARANG PILANG' telah dibuat	{"project_name":"5SBU-KRP-PT3-KARANG PILANG","project_code":"PRJ-2025-08-010","budget":"15000000.00","status":"in_progress"}	2025-08-26 11:07:17	2025-08-26 11:07:17
11	11	1	project_created	Proyek '3SBU-KRP-PT3-KARNGPILANG MERPATI' telah dibuat	{"project_name":"3SBU-KRP-PT3-KARNGPILANG MERPATI","project_code":"PRJ-2025-08-011","budget":"3000000.00","status":"in_progress"}	2025-08-26 11:08:23	2025-08-26 11:08:23
12	9	1	project_updated	Proyek '3SBU-DBS25-PT3-SDY-FB-KCP (1001840019)' telah diperbarui	{"budget":{"from":"500000.00","to":"400000.00"}}	2025-08-26 11:09:15	2025-08-26 11:09:15
13	10	1	project_updated	Proyek '5SBU-KRP-PT3-KARANG PILANG' telah diperbarui	{"budget":{"from":"15000000.00","to":"10000000.00"}}	2025-08-26 11:09:39	2025-08-26 11:09:39
14	8	1	project_updated	Proyek '5SBU-GSK-PT3-JL. SUNAN GIRI GANG 13 E' telah diperbarui	{"budget":{"from":"1000000.00","to":"1300000.00"}}	2025-08-26 11:10:30	2025-08-26 11:10:30
15	9	5	project_updated	Proyek '3SBU-DBS25-PT3-SDY-FB-KCP (1001840019)' telah diperbarui	{"budget":{"from":"400000.00","to":"391360.00"}}	2025-08-26 11:27:57	2025-08-26 11:27:57
16	8	5	project_updated	Proyek '5SBU-GSK-PT3-JL. SUNAN GIRI GANG 13 E' telah diperbarui	{"budget":{"from":"1300000.00","to":"1321390.00"}}	2025-08-26 11:31:13	2025-08-26 11:31:13
17	7	5	project_updated	Proyek '3SBU-GSK-PT3-RUSUNAWA USMAN SADAR' telah diperbarui	{"budget":{"from":"1000000.00","to":"1214460.00"}}	2025-08-26 11:32:09	2025-08-26 11:32:09
18	11	1	project_updated	Status proyek '3SBU-KRP-PT3-KARNGPILANG MERPATI' diubah dari 'in_progress' ke 'completed'	{"status":{"from":"in_progress","to":"completed"}}	2025-08-26 12:03:06	2025-08-26 12:03:06
19	10	1	project_updated	Status proyek '5SBU-KRP-PT3-KARANG PILANG' diubah dari 'in_progress' ke 'completed'	{"status":{"from":"in_progress","to":"completed"}}	2025-08-26 12:03:24	2025-08-26 12:03:24
20	11	5	project_updated	Proyek '3SBU-KRP-PT3-KARNGPILANG MERPATI' telah diperbarui	{"budget":{"from":"3000000.00","to":"3083490.00"}}	2025-08-26 12:03:44	2025-08-26 12:03:44
21	11	1	project_updated	Status proyek '3SBU-KRP-PT3-KARNGPILANG MERPATI' diubah dari 'completed' ke 'in_progress'	{"status":{"from":"completed","to":"in_progress"}}	2025-08-26 12:03:55	2025-08-26 12:03:55
22	10	5	project_updated	Proyek '5SBU-KRP-PT3-KARANG PILANG' telah diperbarui	{"budget":{"from":"10000000.00","to":"10332370.00"}}	2025-08-26 12:04:15	2025-08-26 12:04:15
23	10	5	project_updated	Status proyek '5SBU-KRP-PT3-KARANG PILANG' diubah dari 'completed' ke 'in_progress'	{"status":{"from":"completed","to":"in_progress"}}	2025-08-26 12:04:40	2025-08-26 12:04:40
24	12	1	project_created	Proyek 'GANTI ODP-DMO-FQM/05' telah dibuat	{"project_name":"GANTI ODP-DMO-FQM\\/05","project_code":"PRJ-2025-08-012","budget":"193000.00","status":"completed"}	2025-08-26 12:06:37	2025-08-26 12:06:37
25	13	5	project_created	Proyek 'DULAHOMING INC10764354 SBX364' telah dibuat	{"project_name":"DULAHOMING INC10764354 SBX364","project_code":"PRJ-2025-08-013","budget":"1782850.00","status":"completed"}	2025-08-26 12:07:11	2025-08-26 12:07:11
26	14	1	project_created	Proyek 'GANTI ODP NO GDOCS 5866 ODP-DMO-FX/17' telah dibuat	{"project_name":"GANTI ODP NO GDOCS 5866 ODP-DMO-FX\\/17","project_code":"PRJ-2025-08-014","budget":"472550.00","status":"completed"}	2025-08-26 12:07:18	2025-08-26 12:07:18
27	12	1	project_updated	Proyek 'GANTI ODP-DMO-FQM/05' telah diperbarui	[]	2025-08-26 12:07:36	2025-08-26 12:07:36
28	15	5	project_created	Proyek 'INC38668934 DONE GANTI TIANG JL.PETEMON III NO.74' telah dibuat	{"project_name":"INC38668934 DONE GANTI TIANG JL.PETEMON III NO.74","project_code":"PRJ-2025-08-015","budget":"584010.00","status":"completed"}	2025-08-26 12:08:10	2025-08-26 12:08:10
29	16	1	project_created	Proyek 'IN160540235 GANTI ODP ODP-DMO-FDG/19' telah dibuat	{"project_name":"IN160540235 GANTI ODP ODP-DMO-FDG\\/19","project_code":"PRJ-2025-08-016","budget":"238530.00","status":"completed"}	2025-08-26 12:08:24	2025-08-26 12:08:24
30	17	5	project_created	Proyek 'GANTI ODP ODP-DMO-FDH/10' telah dibuat	{"project_name":"GANTI ODP ODP-DMO-FDH\\/10","project_code":"PRJ-2025-08-017","budget":"426150.00","status":"completed"}	2025-08-26 12:09:04	2025-08-26 12:09:04
31	18	1	project_created	Proyek 'GANTI TIANG SIMO SIDOMULYO  VII' telah dibuat	{"project_name":"GANTI TIANG SIMO SIDOMULYO  VII","project_code":"PRJ-2025-08-018","budget":"369980.00","status":"completed"}	2025-08-26 12:09:06	2025-08-26 12:09:06
32	19	5	project_created	Proyek 'CABUT TIANG JL. PETEMON V NO.69' telah dibuat	{"project_name":"CABUT TIANG JL. PETEMON V NO.69","project_code":"PRJ-2025-08-019","budget":"141590.00","status":"completed"}	2025-08-26 12:09:46	2025-08-26 12:09:46
33	20	1	project_created	Proyek 'INC38770469 GAMAS  DS-DMO-FE-46-05-01/01, DS-DMO-FE-46-05-01/03' telah dibuat	{"project_name":"INC38770469 GAMAS  DS-DMO-FE-46-05-01\\/01, DS-DMO-FE-46-05-01\\/03","project_code":"PRJ-2025-08-020","budget":"6981920.00","status":"completed"}	2025-08-26 12:09:49	2025-08-26 12:09:49
34	21	1	project_created	Proyek 'PENGAWALAN GALIAN PETEMON' telah dibuat	{"project_name":"PENGAWALAN GALIAN PETEMON","project_code":"PRJ-2025-08-021","budget":"476490.00","status":"completed"}	2025-08-26 12:10:19	2025-08-26 12:10:19
35	22	5	project_created	Proyek '2025-07-SBU-QE ACCESS-00179INC37005538 GANTI ODP ODP-DMO-FDG/31' telah dibuat	{"project_name":"2025-07-SBU-QE ACCESS-00179INC37005538 GANTI ODP ODP-DMO-FDG\\/31","project_code":"PRJ-2025-08-022","budget":"333350.00","status":"completed"}	2025-08-26 12:10:29	2025-08-26 12:10:29
36	22	5	project_updated	Proyek '2025-07-SBU-QE ACCESS-00179INC37005538 GANTI ODP ODP-DMO-FDG/31' telah diperbarui	[]	2025-08-26 12:10:37	2025-08-26 12:10:37
37	23	5	project_created	Proyek 'INC20711440  SBY331' telah dibuat	{"project_name":"INC20711440\\u00a0 SBY331","project_code":"PRJ-2025-08-023","budget":"1681250.00","status":"completed"}	2025-08-26 12:11:46	2025-08-26 12:11:46
38	24	5	project_created	Proyek 'PERAPIAN KU Jl. Simo Katerungan No.57' telah dibuat	{"project_name":"PERAPIAN KU Jl. Simo Katerungan No.57","project_code":"PRJ-2025-08-024","budget":"61040.00","status":"completed"}	2025-08-26 12:12:53	2025-08-26 12:12:53
39	25	5	project_created	Proyek 'GANTI ODP  ODP-DMO-FQT/64 /3021' telah dibuat	{"project_name":"GANTI ODP  ODP-DMO-FQT\\/64 \\/3021","project_code":"PRJ-2025-08-025","budget":"331230.00","status":"completed"}	2025-08-26 12:13:35	2025-08-26 12:13:35
40	26	5	project_created	Proyek 'INC38910176 DONE PERMANENSASI DS-DMO-FE-48-03-01' telah dibuat	{"project_name":"INC38910176 DONE PERMANENSASI DS-DMO-FE-48-03-01","project_code":"PRJ-2025-08-026","budget":"3384530.00","status":"completed"}	2025-08-26 12:14:35	2025-08-26 12:14:35
41	27	5	project_created	Proyek '2025-07-SBU-QE ACCESS-00124PERMANENISASI ODP-DMO-FX/10 EMBONG KALIASIN' telah dibuat	{"project_name":"2025-07-SBU-QE ACCESS-00124PERMANENISASI ODP-DMO-FX\\/10 EMBONG KALIASIN","project_code":"PRJ-2025-08-027","budget":"3272300.00","status":"completed"}	2025-08-26 12:15:53	2025-08-26 12:15:53
42	28	1	project_created	Proyek '3MDR-SPG-PT3-DULANG SAMPANG' telah dibuat	{"project_name":"3MDR-SPG-PT3-DULANG SAMPANG","project_code":"PRJ-2025-08-028","budget":"139568815.00","status":"planning"}	2025-08-26 14:20:46	2025-08-26 14:20:46
43	29	1	project_created	Proyek 'KAMPUNG MALANG' telah dibuat	{"project_name":"KAMPUNG MALANG","project_code":"PRJ-2025-08-029","budget":"4000000.00","status":"in_progress"}	2025-08-26 14:45:41	2025-08-26 14:45:41
44	29	1	billing_updated	Status tagihan proyek diperbarui. Total tagihan: 1.000.000 (25%)	\N	2025-08-26 14:47:08	2025-08-26 14:47:08
45	29	1	billing_updated	Status tagihan proyek diperbarui. Total tagihan: 1.000.000 (25%)	\N	2025-08-26 14:47:14	2025-08-26 14:47:14
46	29	1	expense_created	Pengeluaran baru sebesar Rp 200rb untuk 'Kompensasi gagal kerja'	{"expense_id":1,"amount":"200000.00","description":"Kompensasi gagal kerja","category":"other","status":"approved"}	2025-08-26 14:47:48	2025-08-26 14:47:48
47	29	1	expense_created	Pengeluaran baru sebesar Rp 1jt untuk 'DP MANDORAN KE WAHYU DKK TIM MALAM'	{"expense_id":2,"amount":"1000000.00","description":"DP MANDORAN KE WAHYU DKK TIM MALAM","category":"labor","status":"approved"}	2025-08-26 14:48:25	2025-08-26 14:48:25
48	30	5	project_created	Proyek '3SBU-DPS25-PT3-SDY-FAZ-X' telah dibuat	{"project_name":"3SBU-DPS25-PT3-SDY-FAZ-X","project_code":"PRJ-2025-08-030","budget":"5438000.00","status":"in_progress"}	2025-08-26 16:03:26	2025-08-26 16:03:26
49	30	1	project_updated	Proyek '3SBU-DPS25-PT3-SDY-FAZ-X' telah diperbarui	[]	2025-08-26 16:03:41	2025-08-26 16:03:41
50	30	5	project_updated	Proyek '3SBU-DPS25-PT3-SDY-FAZ-X' telah diperbarui	[]	2025-08-26 16:03:44	2025-08-26 16:03:44
51	30	1	expense_created	Pengeluaran baru sebesar Rp 200rb untuk 'BENSIN APV'	{"expense_id":3,"amount":"200000.00","description":"BENSIN APV","category":"transportation","status":"approved"}	2025-08-26 16:04:45	2025-08-26 16:04:45
52	30	5	expense_created	Pengeluaran baru sebesar Rp 200rb untuk 'BENSIN GRANMAX'	{"expense_id":4,"amount":"200000.00","description":"BENSIN GRANMAX","category":"transportation","status":"approved"}	2025-08-26 16:05:02	2025-08-26 16:05:02
53	30	5	expense_created	Pengeluaran baru sebesar Rp 100rb untuk 'ETOL'	{"expense_id":5,"amount":"100000.00","description":"ETOL","category":"transportation","status":"approved"}	2025-08-26 16:06:13	2025-08-26 16:06:13
54	30	5	expense_created	Pengeluaran baru sebesar Rp 50rb untuk 'UANG AIR MINUM'	{"expense_id":6,"amount":"50000.00","description":"UANG AIR MINUM","category":"other","status":"approved"}	2025-08-26 16:06:55	2025-08-26 16:06:55
55	30	5	expense_created	Pengeluaran baru sebesar Rp 100rb untuk 'BENSIN ROKIM'	{"expense_id":7,"amount":"100000.00","description":"BENSIN ROKIM","category":"transportation","status":"approved"}	2025-08-26 16:09:58	2025-08-26 16:09:58
56	31	5	project_created	Proyek '3SBU-BBE-PT3-SARIREJO DALAM DAN LUAR' telah dibuat	{"project_name":"3SBU-BBE-PT3-SARIREJO DALAM DAN LUAR","project_code":"PRJ-2025-08-031","budget":"0.00","status":"in_progress"}	2025-08-27 12:41:13	2025-08-27 12:41:13
57	31	5	expense_created	Pengeluaran baru sebesar Rp 50rb untuk 'pipa pvc'	{"expense_id":8,"amount":"50000.00","description":"pipa pvc","category":"material","status":"approved"}	2025-08-27 12:42:09	2025-08-27 12:42:09
60	28	1	expense_created	Pengeluaran baru sebesar Rp 20jt untuk 'DP 1 KE FERY'	{"expense_id":9,"amount":"20000000.00","description":"DP 1 KE FERY","category":"labor","status":"approved"}	2025-08-27 17:21:27	2025-08-27 17:21:27
62	34	5	project_created	Proyek 'INC39030532 DS-DMO-FE-18-02-02/05 GAMAS IMBAS TERBAKAR' telah dibuat	{"project_name":"INC39030532 DS-DMO-FE-18-02-02\\/05 GAMAS IMBAS TERBAKAR","project_code":"PRJ-2025-08-033","budget":"0.00","status":"completed"}	2025-08-28 10:18:47	2025-08-28 10:18:47
63	28	1	expense_created	Pengeluaran baru sebesar Rp 20jt untuk 'DP KE 1 FERY'	{"expense_id":10,"amount":"20000000.00","description":"DP KE 1 FERY","category":"labor","status":"approved"}	2025-08-28 12:13:21	2025-08-28 12:13:21
64	29	1	cashflow_fixed	Cashflow entry created for paid billing #001/SK-08/2025 (FIX via command)	\N	2025-08-28 12:23:31	2025-08-28 12:23:31
66	28	1	project_updated	Status proyek '3MDR-SPG-PT3-DULANG SAMPANG' diubah dari 'planning' ke 'in_progress'	{"status":{"from":"planning","to":"in_progress"}}	2025-08-28 13:49:11	2025-08-28 13:49:11
67	10	1	project_updated	Status proyek '5SBU-KRP-PT3-KARANG PILANG' diubah dari 'in_progress' ke 'completed'	{"status":{"from":"in_progress","to":"completed"}}	2025-08-28 13:49:39	2025-08-28 13:49:39
68	11	1	project_updated	Status proyek '3SBU-KRP-PT3-KARNGPILANG MERPATI' diubah dari 'in_progress' ke 'completed'	{"status":{"from":"in_progress","to":"completed"}}	2025-08-28 13:50:01	2025-08-28 13:50:01
69	30	5	expense_created	Pengeluaran baru sebesar Rp 400rb untuk 'bensin apv dan granmax'	{"expense_id":11,"amount":"400000.00","description":"bensin apv dan granmax","category":"transportation","status":"approved"}	2025-08-28 15:57:22	2025-08-28 15:57:22
70	30	5	expense_created	Pengeluaran baru sebesar Rp 100rb untuk 'etol'	{"expense_id":12,"amount":"100000.00","description":"etol","category":"transportation","status":"approved"}	2025-08-28 15:57:51	2025-08-28 15:57:51
71	30	5	expense_created	Pengeluaran baru sebesar Rp 50rb untuk 'bensin motor'	{"expense_id":13,"amount":"50000.00","description":"bensin motor","category":"transportation","status":"approved"}	2025-08-28 15:58:23	2025-08-28 15:58:23
72	30	5	expense_created	Pengeluaran baru sebesar Rp 150rb untuk 'bensin ocim'	{"expense_id":14,"amount":"150000.00","description":"bensin ocim","category":"transportation","status":"approved"}	2025-08-28 15:58:44	2025-08-28 15:58:44
73	34	5	project_updated	Proyek 'INC39030532 DS-DMO-FE-18-02-02/05 GAMAS IMBAS TERBAKAR' telah diperbarui	[]	2025-08-28 16:03:41	2025-08-28 16:03:41
74	34	5	project_updated	Proyek 'GAMAS IMBAS TERBAKAR ODP-DMO-FDB/23' telah diperbarui	{"name":{"from":"INC39030532 DS-DMO-FE-18-02-02\\/05 GAMAS IMBAS TERBAKAR","to":"GAMAS IMBAS TERBAKAR ODP-DMO-FDB\\/23"}}	2025-08-28 16:10:53	2025-08-28 16:10:53
75	34	5	project_updated	Proyek 'GAMAS IMBAS TERBAKAR ODP-DMO-FDB/23' telah diperbarui	{"budget":{"from":"0.00","to":"2889450.00"}}	2025-08-28 16:58:08	2025-08-28 16:58:08
76	30	5	project_updated	Proyek '3SBU-DPS25-PT3-GSK-FAZ-X (1-41744453425)' telah diperbarui	{"name":{"from":"3SBU-DPS25-PT3-SDY-FAZ-X","to":"3SBU-DPS25-PT3-GSK-FAZ-X (1-41744453425)"}}	2025-08-28 17:26:27	2025-08-28 17:26:27
77	31	5	expense_created	Pengeluaran baru sebesar Rp 17rb untuk 'tali rafia'	{"expense_id":15,"amount":"17000.00","description":"tali rafia","category":"material","status":"approved"}	2025-08-28 17:53:31	2025-08-28 17:53:31
78	31	5	expense_created	Pengeluaran baru sebesar Rp 50rb untuk 'beli air'	{"expense_id":16,"amount":"50000.00","description":"beli air","category":"other","status":"approved"}	2025-08-28 17:53:52	2025-08-28 17:53:52
79	31	5	expense_created	Pengeluaran baru sebesar Rp 35rb untuk 'klem'	{"expense_id":17,"amount":"35000.00","description":"klem","category":"material","status":"approved"}	2025-08-28 17:55:35	2025-08-28 17:55:35
80	29	1	billing_updated	Status tagihan proyek diperbarui. Total tagihan: 1.000.000 (25%)	\N	2025-08-29 06:46:25	2025-08-29 06:46:25
81	29	1	billing_updated	Status tagihan proyek diperbarui. Total tagihan: 0 (0%)	\N	2025-08-29 06:48:26	2025-08-29 06:48:26
82	29	1	billing_updated	Status tagihan proyek diperbarui. Total tagihan: 1.000.000 (25%)	\N	2025-08-29 06:52:37	2025-08-29 06:52:37
83	29	1	billing_updated	Status tagihan proyek diperbarui. Total tagihan: 1.000.000 (25%)	\N	2025-08-29 06:52:44	2025-08-29 06:52:44
84	29	1	billing_updated	Status tagihan proyek diperbarui. Total tagihan: 0 (0%)	\N	2025-08-29 06:53:22	2025-08-29 06:53:22
85	29	1	billing_updated	Status tagihan proyek diperbarui. Total tagihan: 1.000.000 (25%)	\N	2025-08-29 06:54:16	2025-08-29 06:54:16
86	29	1	billing_updated	Status tagihan proyek diperbarui. Total tagihan: 1.000.000 (25%)	\N	2025-08-29 06:54:50	2025-08-29 06:54:50
87	29	1	billing_updated	Status tagihan proyek diperbarui. Total tagihan: 1.000.000 (25%)	\N	2025-08-29 06:55:19	2025-08-29 06:55:19
88	29	1	billing_updated	Status tagihan proyek diperbarui. Total tagihan: 1.000.000 (25%)	\N	2025-08-29 06:55:29	2025-08-29 06:55:29
89	29	1	billing_updated	Status tagihan proyek diperbarui. Total tagihan: 0 (0%)	\N	2025-08-29 06:55:43	2025-08-29 06:55:43
90	29	1	billing_updated	Status tagihan proyek diperbarui. Total tagihan: 1.000.000 (25%)	\N	2025-08-29 11:18:14	2025-08-29 11:18:14
91	34	5	expense_created	Pengeluaran baru sebesar Rp 900rb untuk 'pembayaran gamas, teknisi wahyu dkk'	{"expense_id":18,"amount":"900000.00","description":"pembayaran gamas, teknisi wahyu dkk","category":"labor","status":"approved"}	2025-08-29 13:05:19	2025-08-29 13:05:19
92	35	5	project_created	Proyek '5509 / DONE GANTI HH PIT ODP-LKS-FKB/13' telah dibuat	{"project_name":"5509 \\/ DONE GANTI HH PIT ODP-LKS-FKB\\/13","project_code":"PRJ-2025-08-034","budget":"768460.00","status":"completed"}	2025-08-29 13:41:59	2025-08-29 13:41:59
\.


--
-- Data for Name: project_billings; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.project_billings (id, project_id, billing_date, status, notes, paid_date, created_at, updated_at, payment_type, termin_number, total_termin, is_final_termin, parent_schedule_id, nilai_jasa, nilai_material, subtotal, ppn_rate, ppn_calculation, ppn_amount, total_amount, invoice_number, sp_number, tax_invoice_number, billing_batch_id, base_amount, pph_amount, received_amount) FROM stdin;
4	29	2025-08-15	paid	\N	2025-08-15	2025-08-29 11:18:14	2025-08-29 11:18:14	termin	1	4	f	\N	1000000.00	0.00	1000000.00	0.00	normal	0.00	1000000.00	001/INV-08/2025	\N	\N	\N	0.00	0.00	0.00
\.


--
-- Data for Name: project_clients; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.project_clients (id, name, usage_count, last_used_at, created_at, updated_at) FROM stdin;
1	TA	28	2025-08-31 09:04:47	2025-08-26 10:02:09	2025-08-31 09:04:47
2	FASTNET	1	2025-08-26 14:45:41	2025-08-26 14:45:41	2025-08-26 14:45:41
\.


--
-- Data for Name: project_documents; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.project_documents (id, project_id, uploaded_by, name, original_name, file_path, file_type, file_size, document_type, description, created_at, updated_at, storage_path, rclone_path, sync_status, sync_error, last_sync_at, checksum, folder_structure) FROM stdin;
\.


--
-- Data for Name: project_expenses; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.project_expenses (id, project_id, user_id, description, amount, expense_date, created_at, updated_at, category, receipt_number, vendor, notes, status) FROM stdin;
1	29	1	Kompensasi gagal kerja	200000.00	2025-08-14	2025-08-26 14:47:48	2025-08-26 14:47:48	other	\N	\N	\N	approved
2	29	1	DP MANDORAN KE WAHYU DKK TIM MALAM	1000000.00	2025-08-16	2025-08-26 14:48:25	2025-08-26 14:48:25	labor	\N	\N	\N	approved
3	30	1	BENSIN APV	200000.00	2025-08-26	2025-08-26 16:04:45	2025-08-26 16:04:45	transportation	\N	\N	\N	approved
4	30	5	BENSIN GRANMAX	200000.00	2025-08-26	2025-08-26 16:05:02	2025-08-26 16:05:02	transportation	\N	\N	\N	approved
5	30	5	ETOL	100000.00	2025-08-26	2025-08-26 16:06:13	2025-08-26 16:06:13	transportation	\N	\N	\N	approved
6	30	5	UANG AIR MINUM	50000.00	2025-08-26	2025-08-26 16:06:55	2025-08-26 16:06:55	other	\N	\N	\N	approved
7	30	5	BENSIN ROKIM	100000.00	2025-08-26	2025-08-26 16:09:58	2025-08-26 16:09:58	transportation	\N	\N	\N	approved
8	31	5	pipa pvc	50000.00	2025-08-27	2025-08-27 12:42:09	2025-08-27 12:42:09	material	\N	\N	\N	approved
10	28	1	DP KE 1 FERY	20000000.00	2025-08-27	2025-08-28 12:13:21	2025-08-28 12:13:21	labor	\N	\N	\N	approved
11	30	5	bensin apv dan granmax	400000.00	2025-08-28	2025-08-28 15:57:22	2025-08-28 15:57:22	transportation	\N	\N	\N	approved
12	30	5	etol	100000.00	2025-08-28	2025-08-28 15:57:51	2025-08-28 15:57:51	transportation	\N	\N	\N	approved
13	30	5	bensin motor	50000.00	2025-08-28	2025-08-28 15:58:22	2025-08-28 15:58:22	transportation	\N	\N	\N	approved
14	30	5	bensin ocim	150000.00	2025-08-28	2025-08-28 15:58:44	2025-08-28 15:58:44	transportation	\N	\N	\N	approved
15	31	5	tali rafia	17000.00	2025-08-27	2025-08-28 17:53:31	2025-08-28 17:53:31	material	\N	\N	\N	approved
16	31	5	beli air	50000.00	2025-08-27	2025-08-28 17:53:52	2025-08-28 17:53:52	other	\N	\N	\N	approved
17	31	5	klem	35000.00	2025-08-27	2025-08-28 17:55:35	2025-08-28 17:55:35	material	\N	\N	\N	approved
18	34	5	pembayaran gamas, teknisi wahyu dkk	900000.00	2025-08-28	2025-08-29 13:05:19	2025-08-29 13:05:19	labor	\N	\N	\N	approved
\.


--
-- Data for Name: project_folders; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.project_folders (id, project_id, folder_name, folder_path, parent_id, folder_type, sync_status, metadata, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: project_locations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.project_locations (id, name, description, usage_count, last_used_at, created_at, updated_at) FROM stdin;
1	BBE	\N	4	2025-08-26 10:03:55	2025-08-26 10:02:09	2025-08-26 10:03:55
4	SDY	\N	3	2025-08-26 11:27:57	2025-08-26 11:06:18	2025-08-26 11:27:57
9	SPG	\N	2	2025-08-28 13:49:11	2025-08-26 14:20:46	2025-08-28 13:49:11
2	KRP	\N	13	2025-08-28 13:50:01	2025-08-26 10:06:48	2025-08-28 13:50:01
5	DMO	\N	1	2025-08-26 12:06:37	2025-08-26 12:06:37	2025-08-26 12:06:37
6	KJR	\N	1	2025-08-26 12:07:11	2025-08-26 12:07:11	2025-08-26 12:07:11
3	GSK	\N	10	2025-08-28 17:26:27	2025-08-26 11:04:21	2025-08-28 17:26:27
10	LKI	\N	1	2025-08-29 13:41:59	2025-08-29 13:41:59	2025-08-29 13:41:59
7	MGO	\N	22	2025-08-31 09:04:47	2025-08-26 12:07:18	2025-08-31 09:04:47
8	MG	\N	1	2025-08-26 12:10:29	2025-08-26 12:10:29	2025-08-26 12:10:29
\.


--
-- Data for Name: project_payment_schedules; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.project_payment_schedules (id, project_id, termin_number, total_termin, termin_name, percentage, amount, due_date, created_date, status, billing_id, description, notes, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: project_profit_analyses; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.project_profit_analyses (id, project_id, total_revenue, total_expenses, net_profit, profit_margin, analysis_notes, improvement_recommendations, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: project_revenues; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.project_revenues (id, project_id, total_amount, net_profit, profit_margin, revenue_date, calculation_details, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: project_timelines; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.project_timelines (id, project_id, milestone, description, planned_date, actual_date, status, progress_percentage, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: projects; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.projects (id, name, description, planned_budget, actual_budget, start_date, end_date, created_at, updated_at, code, location, priority, notes, status, planned_service_value, planned_material_value, planned_total_value, final_service_value, final_material_value, final_total_value, client_type, billing_status, latest_po_number, latest_sp_number, latest_invoice_number, total_billed_amount, billing_percentage, last_billing_date, type, client) FROM stdin;
1	3SBU-BBE-MD PT2 EXPAND-ODP-BBE-FAQ-16	\N	316282.00	0.00	2025-08-06	2025-08-06	2025-08-26 10:02:09	2025-08-26 10:02:09	PRJ-2025-08-001	BBE	low	\N	completed	316282.00	0.00	316282.00	\N	\N	\N	non_wapu	not_billed	\N	\N	\N	0.00	0.00	\N	konstruksi	TA
2	3SBU-BBE-MD PT2 EXPAND-ODP-BBE-FAP-28	\N	316282.00	0.00	2025-08-06	2025-08-06	2025-08-26 10:02:40	2025-08-26 10:02:40	PRJ-2025-08-002	BBE	low	\N	completed	316282.00	0.00	316282.00	\N	\N	\N	non_wapu	not_billed	\N	\N	\N	0.00	0.00	\N	konstruksi	TA
3	3SBU-BBE-MD PT2 EXPAND-ODP-BBE-FAQ-03	\N	316282.00	0.00	2025-08-06	2025-08-06	2025-08-26 10:03:14	2025-08-26 10:03:14	PRJ-2025-08-003	BBE	low	\N	completed	316282.00	0.00	316282.00	\N	\N	\N	non_wapu	not_billed	\N	\N	\N	0.00	0.00	\N	konstruksi	TA
4	3SBU-BBE-MD PT2 EXPAND-ODP-BBE-FAQ-09	\N	316282.00	0.00	2025-08-05	2025-08-05	2025-08-26 10:03:55	2025-08-26 10:03:55	PRJ-2025-08-004	BBE	low	\N	completed	316282.00	0.00	316282.00	\N	\N	\N	non_wapu	not_billed	\N	\N	\N	0.00	0.00	\N	konstruksi	TA
5	3SBU-KRP-PT3-KEMLATEN GG MASJID NO.22	\N	605840.00	0.00	2025-08-07	2025-08-07	2025-08-26 10:06:48	2025-08-26 10:06:48	PRJ-2025-08-005	KRP	medium	\N	completed	605840.00	0.00	605840.00	\N	\N	\N	non_wapu	not_billed	\N	\N	\N	0.00	0.00	\N	konstruksi	TA
6	3SBU-LKI-PT3-APC-ALANA REGENCY GSI	\N	18288140.00	0.00	2025-08-08	2025-08-14	2025-08-26 10:08:15	2025-08-26 10:08:15	PRJ-2025-08-006	KRP	medium	\N	completed	18288140.00	0.00	18288140.00	\N	\N	\N	non_wapu	not_billed	\N	\N	\N	0.00	0.00	\N	konstruksi	TA
22	2025-07-SBU-QE ACCESS-00179INC37005538 GANTI ODP ODP-DMO-FDG/31	\N	333350.00	0.00	2025-08-14	2025-08-14	2025-08-26 12:10:29	2025-08-26 12:10:37	PRJ-2025-08-022	MGO	medium	\N	completed	331230.00	2120.00	333350.00	\N	\N	\N	non_wapu	not_billed	\N	\N	\N	0.00	0.00	\N	maintenance	\N
23	INC20711440  SBY331	\N	1681250.00	0.00	2025-07-09	2025-07-09	2025-08-26 12:11:46	2025-08-26 12:11:46	PRJ-2025-08-023	MGO	medium	\N	completed	1493250.00	188000.00	1681250.00	\N	\N	\N	non_wapu	not_billed	\N	\N	\N	0.00	0.00	\N	maintenance	\N
9	3SBU-DBS25-PT3-SDY-FB-KCP (1001840019)	\N	391360.00	0.00	2025-08-14	2025-08-14	2025-08-26 11:06:18	2025-08-26 11:27:57	PRJ-2025-08-009	SDY	high	KURANG BERKAS V4	completed	391360.00	0.00	391360.00	\N	\N	\N	non_wapu	not_billed	\N	\N	\N	0.00	0.00	\N	konstruksi	\N
8	5SBU-GSK-PT3-JL. SUNAN GIRI GANG 13 E	\N	1321390.00	0.00	2025-08-17	2025-08-17	2025-08-26 11:05:20	2025-08-26 11:31:13	PRJ-2025-08-008	GSK	high	KURANG BERKAS V4	completed	1321390.00	0.00	1321390.00	\N	\N	\N	non_wapu	not_billed	\N	\N	\N	0.00	0.00	\N	konstruksi	\N
7	3SBU-GSK-PT3-RUSUNAWA USMAN SADAR	\N	1214460.00	0.00	2025-08-17	2025-08-17	2025-08-26 11:04:21	2025-08-26 11:32:09	PRJ-2025-08-007	GSK	high	KURANG BERKAS V4	completed	1214460.00	0.00	1214460.00	\N	\N	\N	non_wapu	not_billed	\N	\N	\N	0.00	0.00	\N	konstruksi	\N
24	PERAPIAN KU Jl. Simo Katerungan No.57	\N	61040.00	0.00	2025-08-19	2025-08-19	2025-08-26 12:12:53	2025-08-26 12:12:53	PRJ-2025-08-024	MGO	medium	\N	completed	61040.00	0.00	61040.00	\N	\N	\N	non_wapu	not_billed	\N	\N	\N	0.00	0.00	\N	maintenance	\N
25	GANTI ODP  ODP-DMO-FQT/64 /3021	\N	331230.00	0.00	2025-08-19	2025-08-19	2025-08-26 12:13:35	2025-08-26 12:13:35	PRJ-2025-08-025	MGO	medium	\N	completed	331230.00	0.00	331230.00	\N	\N	\N	non_wapu	not_billed	\N	\N	\N	0.00	0.00	\N	maintenance	\N
26	INC38910176 DONE PERMANENSASI DS-DMO-FE-48-03-01	\N	3384530.00	0.00	2025-08-20	2025-08-20	2025-08-26 12:14:35	2025-08-26 12:14:35	PRJ-2025-08-026	MGO	medium	\N	completed	3161530.00	223000.00	3384530.00	\N	\N	\N	non_wapu	not_billed	\N	\N	\N	0.00	0.00	\N	maintenance	\N
34	GAMAS IMBAS TERBAKAR ODP-DMO-FDB/23	\N	2889450.00	0.00	2025-08-27	2025-08-27	2025-08-28 10:18:47	2025-08-28 16:58:08	PRJ-2025-08-033	MGO	urgent	dibayarkan ke teknisi 900	completed	2777950.00	111500.00	2889450.00	\N	\N	\N	non_wapu	not_billed	\N	\N	\N	0.00	0.00	\N	maintenance	TA
27	2025-07-SBU-QE ACCESS-00124PERMANENISASI ODP-DMO-FX/10 EMBONG KALIASIN	\N	3272300.00	0.00	2025-08-21	2025-08-21	2025-08-26 12:15:53	2025-08-26 12:15:53	PRJ-2025-08-027	MGO	medium	\N	completed	3049300.00	223000.00	3272300.00	\N	\N	\N	non_wapu	not_billed	\N	\N	\N	0.00	0.00	\N	maintenance	\N
13	DULAHOMING INC10764354 SBX364	\N	1782850.00	0.00	2025-07-27	2025-07-27	2025-08-26 12:07:11	2025-08-26 12:07:11	PRJ-2025-08-013	KJR	medium	\N	completed	1594850.00	188000.00	1782850.00	\N	\N	\N	non_wapu	not_billed	\N	\N	\N	0.00	0.00	\N	maintenance	\N
14	GANTI ODP NO GDOCS 5866 ODP-DMO-FX/17	\N	472550.00	0.00	2025-08-15	2025-08-15	2025-08-26 12:07:18	2025-08-26 12:07:18	PRJ-2025-08-014	MGO	low	\N	completed	468840.00	3710.00	472550.00	\N	\N	\N	non_wapu	not_billed	\N	\N	\N	0.00	0.00	\N	maintenance	TA
12	GANTI ODP-DMO-FQM/05	\N	193000.00	0.00	2025-08-15	2025-08-15	2025-08-26 12:06:37	2025-08-26 12:07:36	PRJ-2025-08-012	MGO	low	\N	completed	193000.00	0.00	193000.00	\N	\N	\N	non_wapu	not_billed	\N	\N	\N	0.00	0.00	\N	maintenance	TA
15	INC38668934 DONE GANTI TIANG JL.PETEMON III NO.74	\N	584010.00	0.00	2025-08-14	2025-08-14	2025-08-26 12:08:10	2025-08-26 12:08:10	PRJ-2025-08-015	MGO	medium	\N	completed	584010.00	0.00	584010.00	\N	\N	\N	non_wapu	not_billed	\N	\N	\N	0.00	0.00	\N	maintenance	\N
16	IN160540235 GANTI ODP ODP-DMO-FDG/19	\N	238530.00	0.00	2025-08-15	2025-08-15	2025-08-26 12:08:24	2025-08-26 12:08:24	PRJ-2025-08-016	MGO	low	\N	completed	238530.00	0.00	238530.00	\N	\N	\N	non_wapu	not_billed	\N	\N	\N	0.00	0.00	\N	maintenance	TA
17	GANTI ODP ODP-DMO-FDH/10	\N	426150.00	0.00	2025-08-14	2025-08-14	2025-08-26 12:09:04	2025-08-26 12:09:04	PRJ-2025-08-017	MGO	medium	\N	completed	422970.00	3180.00	426150.00	\N	\N	\N	non_wapu	not_billed	\N	\N	\N	0.00	0.00	\N	maintenance	\N
18	GANTI TIANG SIMO SIDOMULYO  VII	\N	369980.00	0.00	2025-08-15	2025-08-15	2025-08-26 12:09:06	2025-08-26 12:09:06	PRJ-2025-08-018	MGO	low	\N	completed	369980.00	0.00	369980.00	\N	\N	\N	non_wapu	not_billed	\N	\N	\N	0.00	0.00	\N	maintenance	TA
19	CABUT TIANG JL. PETEMON V NO.69	\N	141590.00	0.00	2025-08-14	2025-08-14	2025-08-26 12:09:46	2025-08-26 12:09:46	PRJ-2025-08-019	MGO	medium	\N	completed	141590.00	0.00	141590.00	\N	\N	\N	non_wapu	not_billed	\N	\N	\N	0.00	0.00	\N	maintenance	\N
20	INC38770469 GAMAS  DS-DMO-FE-46-05-01/01, DS-DMO-FE-46-05-01/03	\N	6981920.00	0.00	2025-08-17	2025-08-17	2025-08-26 12:09:49	2025-08-26 12:09:49	PRJ-2025-08-020	MGO	urgent	\N	completed	6312920.00	669000.00	6981920.00	\N	\N	\N	non_wapu	not_billed	\N	\N	\N	0.00	0.00	\N	maintenance	TA
21	PENGAWALAN GALIAN PETEMON	\N	476490.00	0.00	2025-08-12	2025-08-12	2025-08-26 12:10:19	2025-08-26 12:10:19	PRJ-2025-08-021	MGO	medium	\N	completed	476490.00	0.00	476490.00	\N	\N	\N	non_wapu	not_billed	\N	\N	\N	0.00	0.00	\N	maintenance	TA
31	3SBU-BBE-PT3-SARIREJO DALAM DAN LUAR	\N	0.00	0.00	2025-08-27	\N	2025-08-27 12:41:12	2025-08-27 12:41:12	PRJ-2025-08-031	\N	high	\N	in_progress	0.00	0.00	0.00	\N	\N	\N	non_wapu	not_billed	\N	\N	\N	0.00	0.00	\N	konstruksi	TA
28	3MDR-SPG-PT3-DULANG SAMPANG	\N	139568815.00	0.00	2025-08-26	\N	2025-08-26 14:20:46	2025-08-28 13:49:11	PRJ-2025-08-028	SPG	medium	\N	in_progress	139568815.00	0.00	139568815.00	\N	\N	\N	non_wapu	not_billed	\N	\N	\N	0.00	0.00	\N	konstruksi	TA
10	5SBU-KRP-PT3-KARANG PILANG	\N	10332370.00	0.00	2025-08-22	2025-08-27	2025-08-26 11:07:17	2025-08-28 13:49:39	PRJ-2025-08-010	KRP	medium	KURANG PASANG ODP	completed	10332370.00	0.00	10332370.00	\N	\N	\N	non_wapu	not_billed	\N	\N	\N	0.00	0.00	\N	konstruksi	\N
11	3SBU-KRP-PT3-KARNGPILANG MERPATI	\N	3083490.00	0.00	2025-08-25	2025-08-27	2025-08-26 11:08:23	2025-08-28 13:50:01	PRJ-2025-08-011	KRP	medium	KURANG ODP	completed	3083490.00	0.00	3083490.00	\N	\N	\N	non_wapu	not_billed	\N	\N	\N	0.00	0.00	\N	konstruksi	TA
30	3SBU-DPS25-PT3-GSK-FAZ-X (1-41744453425)	\N	5438000.00	0.00	2025-08-26	\N	2025-08-26 16:03:26	2025-08-28 17:26:27	PRJ-2025-08-030	GSK	medium	\N	in_progress	5438000.00	0.00	5438000.00	\N	\N	\N	non_wapu	not_billed	\N	\N	\N	0.00	0.00	\N	konstruksi	TA
29	KAMPUNG MALANG	Kendala perijinan rw belum clear	4000000.00	0.00	2025-08-11	\N	2025-08-26 14:45:41	2025-08-29 11:18:14	PRJ-2025-08-029	MGO	low	\N	in_progress	4000000.00	0.00	4000000.00	\N	\N	\N	non_wapu	partially_billed	\N	\N	001/INV-08/2025	1000000.00	25.00	2025-08-15	konstruksi	FASTNET
35	5509 / DONE GANTI HH PIT ODP-LKS-FKB/13	\N	768460.00	0.00	2025-08-21	2025-08-21	2025-08-29 13:41:59	2025-08-29 13:41:59	PRJ-2025-08-034	LKI	medium	\N	completed	186070.00	582390.00	768460.00	\N	\N	\N	non_wapu	not_billed	\N	\N	\N	0.00	0.00	\N	maintenance	TA
\.


--
-- Data for Name: revenue_items; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.revenue_items (id, project_id, revenue_id, item_name, description, amount, type, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: role_users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.role_users (id, user_id, role_id, created_at, updated_at) FROM stdin;
5	1	1	\N	\N
6	5	1	\N	\N
7	6	1	\N	\N
9	7	1	\N	\N
\.


--
-- Data for Name: roles; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.roles (id, name, description, created_at, updated_at) FROM stdin;
1	direktur	Direktur perusahaan dengan akses penuh	2025-08-25 13:47:16	2025-08-25 13:47:16
2	project_manager	Manager proyek dengan akses manajemen proyek	2025-08-25 13:47:16	2025-08-25 13:47:16
3	finance_manager	Manager keuangan dengan akses manajemen keuangan	2025-08-25 13:47:16	2025-08-25 13:47:16
4	staf	Staf dengan akses input data dasar	2025-08-25 13:47:16	2025-08-25 13:47:16
\.


--
-- Data for Name: salary_releases; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.salary_releases (id, employee_id, release_code, period_start, period_end, total_days, total_amount, deductions, net_amount, release_date, status, notes, cashflow_entry_id, created_by, released_by, created_at, updated_at, deleted_at, released_at, paid_at) FROM stdin;
1	5	SR20250829U2QA	2025-08-11	2025-08-24	0	1785000.00	500000.00	1285000.00	\N	released	\N	26	5	5	2025-08-29 14:19:33	2025-08-29 14:19:41	\N	2025-08-29 14:19:41	\N
2	3	SR20250829ODUG	2025-08-11	2025-08-25	0	1775000.00	0.00	1775000.00	\N	released	\N	27	5	5	2025-08-29 14:23:27	2025-08-29 14:23:32	\N	2025-08-29 14:23:32	\N
3	7	SR20250829QVER	2025-08-11	2025-08-24	0	1835000.00	300000.00	1535000.00	\N	released	\N	28	5	5	2025-08-29 14:35:34	2025-08-29 14:35:40	\N	2025-08-29 14:35:40	\N
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
DXWg3P0gGA3gmZf1WKSSMtzIY1UPGCxwHRtZol2t	1	162.158.22.101	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36	YTo0OntzOjY6Il90b2tlbiI7czo0MDoidFdxMzg0M09oZkJhRWNmSkowYXNFdDlES3FtMHFEZlRuVzZ5WlRsOCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NjA6Imh0dHBzOi8vcHJveWVrLmNsb3VkbmV4aWZ5LmNvbS9hcGkvc3lzdGVtLXN0YXRpc3RpY3MvbWV0cmljcyI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==	1756613133
j5JV3cuUuPIneZ01VKdoo5tOz7oNRiBXjMuF1P72	1	172.68.234.236	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36	YTo0OntzOjY6Il90b2tlbiI7czo0MDoiV214U1c5ZlhNbWpqU2J5Q1NYcTZsOUxlS0t0b0RMQzJZalZTMGIxOSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NjA6Imh0dHBzOi8vcHJveWVrLmNsb3VkbmV4aWZ5LmNvbS9hcGkvc3lzdGVtLXN0YXRpc3RpY3MvbWV0cmljcyI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==	1756613191
AyPXcMxUop13AUv7B6yFeX4TdtMbCs0njGEkVjXB	1	172.71.124.177	Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Mobile Safari/537.36	YTo0OntzOjY6Il90b2tlbiI7czo0MDoid0lpM093SkV4UmFZQUhmVTJXVFU5cnpmQzUxTjdyb1Y2TFc5UU9GQyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzY6Imh0dHBzOi8vcHJveWVrLmNsb3VkbmV4aWZ5LmNvbS9sb2dpbiI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==	1756611859
mDXMSFhkTamUDmXBRcpfe0IcDGHSzxsoCZwi5O8S	1	162.158.107.22	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36	YTo0OntzOjY6Il90b2tlbiI7czo0MDoiY05ZUWE4bUN2NlM5MlZscmF2ZUNaWTNhaTlwYTVrVVBXV09tdWJLVCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NjA6Imh0dHBzOi8vcHJveWVrLmNsb3VkbmV4aWZ5LmNvbS9hcGkvc3lzdGVtLXN0YXRpc3RpY3MvbWV0cmljcyI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==	1756613231
odnjLAC5PCaJdEOyrDsq2ssMcQzUFQDN1n2xnuK9	1	172.71.124.177	Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Mobile Safari/537.36	YTo0OntzOjY6Il90b2tlbiI7czo0MDoiaUZVRGs5dEluRzdmRklxVW5HWmV3WTR0djR3VThTT3RxQVZZa09laiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NTg6Imh0dHBzOi8vcHJveWVrLmNsb3VkbmV4aWZ5LmNvbS9hcGkvZGFzaGJvYXJkL3Byb2plY3QtdHlwZXMiO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=	1756611861
\.


--
-- Data for Name: settings; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.settings (id, key, value, description, type, created_at, updated_at) FROM stdin;
1	expense_director_bypass_enabled	1	Enable director to bypass expense approval workflow	boolean	2025-08-25 13:48:20	2025-08-25 13:48:20
2	expense_high_amount_threshold	10000000	Amount threshold for high-value expenses requiring director approval (in Rupiah)	integer	2025-08-25 13:48:37	2025-08-25 13:48:37
3	expense_approval_notification_enabled	1	Send email notifications for expense approvals	boolean	2025-08-29 06:38:19	2025-08-29 06:38:19
4	salary_cutoff_start_day	11	Tanggal mulai periode gaji (1-31)	integer	2025-08-29 06:38:19	2025-08-29 06:38:19
5	salary_cutoff_end_day	10	Tanggal akhir periode gaji (1-31)	integer	2025-08-29 06:38:19	2025-08-29 06:38:19
6	salary_status_complete_threshold	90	Persentase minimum untuk status lengkap (%)	integer	2025-08-29 06:38:19	2025-08-29 06:38:19
7	salary_status_partial_threshold	50	Persentase minimum untuk status kurang (%)	integer	2025-08-29 06:38:19	2025-08-29 06:38:19
8	salary_status_auto_refresh	1	Auto refresh status setiap 5 menit (0=off, 1=on)	boolean	2025-08-29 06:38:19	2025-08-29 06:38:19
9	salary_status_email_notification	1	Email notification untuk status rendah (0=off, 1=on)	boolean	2025-08-29 06:38:19	2025-08-29 06:38:19
\.


--
-- Data for Name: sync_logs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sync_logs (id, syncable_type, syncable_id, action, status, source_path, destination_path, file_size, duration_ms, error_message, rclone_output, created_at) FROM stdin;
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (id, name, email, email_verified_at, password, remember_token, created_at, updated_at, company_name, company_address, company_phone, company_email) FROM stdin;
1	Ardani	ardani@cloudnexify.com	2025-08-25 13:47:21	$2y$12$1HQflI6VZg3hdteiFrg0g.aYm4Rc7ecuEspK.J6EIeIq3HeVmMeQa	\N	2025-08-25 13:47:21	2025-08-25 13:49:38	PT ARDANI	\N	\N	ardani@cloudnexify.com
6	Ochim	ochim@cloudnexify.com	\N	$2y$12$gZTu86ETIXb7ussFAvkB5.Ct2i8lhhoVP/MYP5k/ctWvnDuGqksAu	\N	2025-08-26 12:13:46	2025-08-26 12:13:46	\N	\N	\N	\N
5	MOMO	momo@cloudnexify.com	\N	$2y$12$9FnVORhEyQwM.Co.QYdZYuj8WAD.bLaLULFhUq9Rh2oSngA8L1aau	XToPQDwyr1vh6OHEjaVkH4tqWTmR8zaXhhWFauj9odYusXqRqyARi07nhNci	2025-08-25 13:50:18	2025-08-25 13:50:18	\N	\N	\N	\N
7	Direktur Mitra	direktur@mitra.com	2025-08-29 06:38:20	$2y$12$4pwH6rr7JIxsRp9hHLXzd.xK.37Bzr4TpbXc6Dy.pBsquvsffZTbq	\N	2025-08-29 06:38:20	2025-08-29 06:40:21	\N	\N	\N	\N
\.


--
-- Name: billing_batches_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.billing_batches_id_seq', 1, false);


--
-- Name: billing_documents_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.billing_documents_id_seq', 1, false);


--
-- Name: billing_status_logs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.billing_status_logs_id_seq', 1, false);


--
-- Name: cashflow_categories_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.cashflow_categories_id_seq', 62, true);


--
-- Name: cashflow_entries_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.cashflow_entries_id_seq', 30, true);


--
-- Name: companies_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.companies_id_seq', 1, false);


--
-- Name: daily_salaries_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.daily_salaries_id_seq', 152, true);


--
-- Name: employee_custom_off_days_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.employee_custom_off_days_id_seq', 1, false);


--
-- Name: employee_work_schedules_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.employee_work_schedules_id_seq', 1, false);


--
-- Name: employees_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.employees_id_seq', 10, true);


--
-- Name: expense_approvals_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.expense_approvals_id_seq', 1, false);


--
-- Name: expense_modification_approvals_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.expense_modification_approvals_id_seq', 1, true);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- Name: import_logs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.import_logs_id_seq', 1, false);


--
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.jobs_id_seq', 1, false);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.migrations_id_seq', 71, true);


--
-- Name: project_activities_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.project_activities_id_seq', 94, true);


--
-- Name: project_billings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.project_billings_id_seq', 4, true);


--
-- Name: project_clients_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.project_clients_id_seq', 2, true);


--
-- Name: project_documents_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.project_documents_id_seq', 6, true);


--
-- Name: project_expenses_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.project_expenses_id_seq', 18, true);


--
-- Name: project_folders_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.project_folders_id_seq', 37, true);


--
-- Name: project_locations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.project_locations_id_seq', 10, true);


--
-- Name: project_payment_schedules_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.project_payment_schedules_id_seq', 1, false);


--
-- Name: project_profit_analyses_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.project_profit_analyses_id_seq', 1, false);


--
-- Name: project_revenues_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.project_revenues_id_seq', 1, false);


--
-- Name: project_timelines_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.project_timelines_id_seq', 1, false);


--
-- Name: projects_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.projects_id_seq', 36, true);


--
-- Name: revenue_items_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.revenue_items_id_seq', 1, false);


--
-- Name: role_users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.role_users_id_seq', 9, true);


--
-- Name: roles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.roles_id_seq', 4, true);


--
-- Name: salary_releases_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.salary_releases_id_seq', 3, true);


--
-- Name: settings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.settings_id_seq', 9, true);


--
-- Name: sync_logs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.sync_logs_id_seq', 1, false);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_id_seq', 7, true);


--
-- Name: billing_batches billing_batches_batch_code_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.billing_batches
    ADD CONSTRAINT billing_batches_batch_code_unique UNIQUE (batch_code);


--
-- Name: billing_batches billing_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.billing_batches
    ADD CONSTRAINT billing_batches_pkey PRIMARY KEY (id);


--
-- Name: billing_documents billing_documents_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.billing_documents
    ADD CONSTRAINT billing_documents_pkey PRIMARY KEY (id);


--
-- Name: billing_status_logs billing_status_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.billing_status_logs
    ADD CONSTRAINT billing_status_logs_pkey PRIMARY KEY (id);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: cashflow_categories cashflow_categories_code_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cashflow_categories
    ADD CONSTRAINT cashflow_categories_code_unique UNIQUE (code);


--
-- Name: cashflow_categories cashflow_categories_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cashflow_categories
    ADD CONSTRAINT cashflow_categories_pkey PRIMARY KEY (id);


--
-- Name: cashflow_entries cashflow_entries_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cashflow_entries
    ADD CONSTRAINT cashflow_entries_pkey PRIMARY KEY (id);


--
-- Name: companies companies_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.companies
    ADD CONSTRAINT companies_pkey PRIMARY KEY (id);


--
-- Name: daily_salaries daily_salaries_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.daily_salaries
    ADD CONSTRAINT daily_salaries_pkey PRIMARY KEY (id);


--
-- Name: employee_custom_off_days employee_custom_off_days_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.employee_custom_off_days
    ADD CONSTRAINT employee_custom_off_days_pkey PRIMARY KEY (id);


--
-- Name: employee_work_schedules employee_work_schedules_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.employee_work_schedules
    ADD CONSTRAINT employee_work_schedules_pkey PRIMARY KEY (id);


--
-- Name: employees employees_employee_code_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.employees
    ADD CONSTRAINT employees_employee_code_unique UNIQUE (employee_code);


--
-- Name: employees employees_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.employees
    ADD CONSTRAINT employees_pkey PRIMARY KEY (id);


--
-- Name: expense_approvals expense_approvals_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.expense_approvals
    ADD CONSTRAINT expense_approvals_pkey PRIMARY KEY (id);


--
-- Name: expense_modification_approvals expense_modification_approvals_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.expense_modification_approvals
    ADD CONSTRAINT expense_modification_approvals_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: import_logs import_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.import_logs
    ADD CONSTRAINT import_logs_pkey PRIMARY KEY (id);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: project_activities project_activities_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_activities
    ADD CONSTRAINT project_activities_pkey PRIMARY KEY (id);


--
-- Name: project_billings project_billings_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_billings
    ADD CONSTRAINT project_billings_pkey PRIMARY KEY (id);


--
-- Name: project_clients project_clients_name_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_clients
    ADD CONSTRAINT project_clients_name_unique UNIQUE (name);


--
-- Name: project_clients project_clients_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_clients
    ADD CONSTRAINT project_clients_pkey PRIMARY KEY (id);


--
-- Name: project_documents project_documents_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_documents
    ADD CONSTRAINT project_documents_pkey PRIMARY KEY (id);


--
-- Name: project_expenses project_expenses_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_expenses
    ADD CONSTRAINT project_expenses_pkey PRIMARY KEY (id);


--
-- Name: project_folders project_folders_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_folders
    ADD CONSTRAINT project_folders_pkey PRIMARY KEY (id);


--
-- Name: project_locations project_locations_name_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_locations
    ADD CONSTRAINT project_locations_name_unique UNIQUE (name);


--
-- Name: project_locations project_locations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_locations
    ADD CONSTRAINT project_locations_pkey PRIMARY KEY (id);


--
-- Name: project_payment_schedules project_payment_schedules_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_payment_schedules
    ADD CONSTRAINT project_payment_schedules_pkey PRIMARY KEY (id);


--
-- Name: project_payment_schedules project_payment_schedules_project_id_termin_number_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_payment_schedules
    ADD CONSTRAINT project_payment_schedules_project_id_termin_number_unique UNIQUE (project_id, termin_number);


--
-- Name: project_profit_analyses project_profit_analyses_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_profit_analyses
    ADD CONSTRAINT project_profit_analyses_pkey PRIMARY KEY (id);


--
-- Name: project_revenues project_revenues_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_revenues
    ADD CONSTRAINT project_revenues_pkey PRIMARY KEY (id);


--
-- Name: project_timelines project_timelines_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_timelines
    ADD CONSTRAINT project_timelines_pkey PRIMARY KEY (id);


--
-- Name: projects projects_code_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.projects
    ADD CONSTRAINT projects_code_unique UNIQUE (code);


--
-- Name: projects projects_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.projects
    ADD CONSTRAINT projects_pkey PRIMARY KEY (id);


--
-- Name: revenue_items revenue_items_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.revenue_items
    ADD CONSTRAINT revenue_items_pkey PRIMARY KEY (id);


--
-- Name: role_users role_users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_users
    ADD CONSTRAINT role_users_pkey PRIMARY KEY (id);


--
-- Name: role_users role_users_user_id_role_id_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_users
    ADD CONSTRAINT role_users_user_id_role_id_unique UNIQUE (user_id, role_id);


--
-- Name: roles roles_name_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_name_unique UNIQUE (name);


--
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- Name: salary_releases salary_releases_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.salary_releases
    ADD CONSTRAINT salary_releases_pkey PRIMARY KEY (id);


--
-- Name: salary_releases salary_releases_release_code_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.salary_releases
    ADD CONSTRAINT salary_releases_release_code_unique UNIQUE (release_code);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: settings settings_key_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.settings
    ADD CONSTRAINT settings_key_unique UNIQUE (key);


--
-- Name: settings settings_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.settings
    ADD CONSTRAINT settings_pkey PRIMARY KEY (id);


--
-- Name: sync_logs sync_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sync_logs
    ADD CONSTRAINT sync_logs_pkey PRIMARY KEY (id);


--
-- Name: employee_custom_off_days unique_employee_off_date; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.employee_custom_off_days
    ADD CONSTRAINT unique_employee_off_date UNIQUE (employee_id, off_date);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: billing_batches_batch_code_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX billing_batches_batch_code_index ON public.billing_batches USING btree (batch_code);


--
-- Name: billing_batches_billing_date_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX billing_batches_billing_date_index ON public.billing_batches USING btree (billing_date);


--
-- Name: billing_batches_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX billing_batches_status_index ON public.billing_batches USING btree (status);


--
-- Name: billing_documents_billing_batch_id_stage_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX billing_documents_billing_batch_id_stage_index ON public.billing_documents USING btree (billing_batch_id, stage);


--
-- Name: billing_documents_document_type_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX billing_documents_document_type_index ON public.billing_documents USING btree (document_type);


--
-- Name: billing_status_logs_billing_batch_id_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX billing_status_logs_billing_batch_id_created_at_index ON public.billing_status_logs USING btree (billing_batch_id, created_at);


--
-- Name: billing_status_logs_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX billing_status_logs_status_index ON public.billing_status_logs USING btree (status);


--
-- Name: cashflow_categories_group_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX cashflow_categories_group_index ON public.cashflow_categories USING btree ("group");


--
-- Name: cashflow_categories_sort_order_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX cashflow_categories_sort_order_index ON public.cashflow_categories USING btree (sort_order);


--
-- Name: cashflow_entries_project_id_transaction_date_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX cashflow_entries_project_id_transaction_date_index ON public.cashflow_entries USING btree (project_id, transaction_date);


--
-- Name: cashflow_entries_reference_type_reference_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX cashflow_entries_reference_type_reference_id_index ON public.cashflow_entries USING btree (reference_type, reference_id);


--
-- Name: cashflow_entries_status_transaction_date_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX cashflow_entries_status_transaction_date_index ON public.cashflow_entries USING btree (status, transaction_date);


--
-- Name: cashflow_entries_transaction_date_type_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX cashflow_entries_transaction_date_type_index ON public.cashflow_entries USING btree (transaction_date, type);


--
-- Name: daily_salaries_employee_id_work_date_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX daily_salaries_employee_id_work_date_index ON public.daily_salaries USING btree (employee_id, work_date);


--
-- Name: daily_salaries_salary_release_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX daily_salaries_salary_release_id_index ON public.daily_salaries USING btree (salary_release_id);


--
-- Name: daily_salaries_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX daily_salaries_status_index ON public.daily_salaries USING btree (status);


--
-- Name: daily_salaries_work_date_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX daily_salaries_work_date_index ON public.daily_salaries USING btree (work_date);


--
-- Name: employees_department_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX employees_department_index ON public.employees USING btree (department);


--
-- Name: employees_email_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX employees_email_index ON public.employees USING btree (email);


--
-- Name: employees_employee_code_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX employees_employee_code_index ON public.employees USING btree (employee_code);


--
-- Name: employees_employment_type_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX employees_employment_type_index ON public.employees USING btree (employment_type);


--
-- Name: employees_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX employees_status_index ON public.employees USING btree (status);


--
-- Name: expense_modification_approvals_approved_by_approved_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX expense_modification_approvals_approved_by_approved_at_index ON public.expense_modification_approvals USING btree (approved_by, approved_at);


--
-- Name: expense_modification_approvals_expense_id_action_type_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX expense_modification_approvals_expense_id_action_type_index ON public.expense_modification_approvals USING btree (expense_id, action_type);


--
-- Name: expense_modification_approvals_requested_by_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX expense_modification_approvals_requested_by_status_index ON public.expense_modification_approvals USING btree (requested_by, status);


--
-- Name: expense_modification_approvals_status_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX expense_modification_approvals_status_created_at_index ON public.expense_modification_approvals USING btree (status, created_at);


--
-- Name: idx_effective_period; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_effective_period ON public.employee_work_schedules USING btree (effective_from, effective_until);


--
-- Name: idx_employee_active; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_employee_active ON public.employee_work_schedules USING btree (employee_id, is_active);


--
-- Name: idx_employee_period; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_employee_period ON public.employee_custom_off_days USING btree (employee_id, period_year, period_month);


--
-- Name: idx_off_date; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_off_date ON public.employee_custom_off_days USING btree (off_date);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: project_billings_billing_batch_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX project_billings_billing_batch_id_index ON public.project_billings USING btree (billing_batch_id);


--
-- Name: project_billings_payment_type_termin_number_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX project_billings_payment_type_termin_number_index ON public.project_billings USING btree (payment_type, termin_number);


--
-- Name: project_billings_project_id_payment_type_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX project_billings_project_id_payment_type_index ON public.project_billings USING btree (project_id, payment_type);


--
-- Name: project_clients_name_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX project_clients_name_index ON public.project_clients USING btree (name);


--
-- Name: project_clients_usage_count_last_used_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX project_clients_usage_count_last_used_at_index ON public.project_clients USING btree (usage_count, last_used_at);


--
-- Name: project_documents_last_sync_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX project_documents_last_sync_at_index ON public.project_documents USING btree (last_sync_at);


--
-- Name: project_documents_sync_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX project_documents_sync_status_index ON public.project_documents USING btree (sync_status);


--
-- Name: project_folders_parent_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX project_folders_parent_id_index ON public.project_folders USING btree (parent_id);


--
-- Name: project_folders_project_id_folder_path_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX project_folders_project_id_folder_path_index ON public.project_folders USING btree (project_id, folder_path);


--
-- Name: project_locations_name_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX project_locations_name_index ON public.project_locations USING btree (name);


--
-- Name: project_locations_usage_count_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX project_locations_usage_count_index ON public.project_locations USING btree (usage_count);


--
-- Name: project_payment_schedules_due_date_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX project_payment_schedules_due_date_status_index ON public.project_payment_schedules USING btree (due_date, status);


--
-- Name: project_payment_schedules_project_id_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX project_payment_schedules_project_id_status_index ON public.project_payment_schedules USING btree (project_id, status);


--
-- Name: project_payment_schedules_project_id_termin_number_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX project_payment_schedules_project_id_termin_number_index ON public.project_payment_schedules USING btree (project_id, termin_number);


--
-- Name: projects_billing_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX projects_billing_status_index ON public.projects USING btree (billing_status);


--
-- Name: projects_last_billing_date_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX projects_last_billing_date_index ON public.projects USING btree (last_billing_date);


--
-- Name: salary_releases_employee_id_period_start_period_end_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX salary_releases_employee_id_period_start_period_end_index ON public.salary_releases USING btree (employee_id, period_start, period_end);


--
-- Name: salary_releases_release_code_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX salary_releases_release_code_index ON public.salary_releases USING btree (release_code);


--
-- Name: salary_releases_release_date_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX salary_releases_release_date_index ON public.salary_releases USING btree (release_date);


--
-- Name: salary_releases_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX salary_releases_status_index ON public.salary_releases USING btree (status);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: sync_logs_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sync_logs_created_at_index ON public.sync_logs USING btree (created_at);


--
-- Name: sync_logs_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sync_logs_status_index ON public.sync_logs USING btree (status);


--
-- Name: sync_logs_syncable_type_syncable_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sync_logs_syncable_type_syncable_id_index ON public.sync_logs USING btree (syncable_type, syncable_id);


--
-- Name: unique_employee_work_date_not_deleted; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX unique_employee_work_date_not_deleted ON public.daily_salaries USING btree (employee_id, work_date) WHERE (deleted_at IS NULL);


--
-- Name: billing_documents billing_documents_billing_batch_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.billing_documents
    ADD CONSTRAINT billing_documents_billing_batch_id_foreign FOREIGN KEY (billing_batch_id) REFERENCES public.billing_batches(id) ON DELETE CASCADE;


--
-- Name: billing_documents billing_documents_uploaded_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.billing_documents
    ADD CONSTRAINT billing_documents_uploaded_by_foreign FOREIGN KEY (uploaded_by) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: billing_status_logs billing_status_logs_billing_batch_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.billing_status_logs
    ADD CONSTRAINT billing_status_logs_billing_batch_id_foreign FOREIGN KEY (billing_batch_id) REFERENCES public.billing_batches(id) ON DELETE CASCADE;


--
-- Name: billing_status_logs billing_status_logs_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.billing_status_logs
    ADD CONSTRAINT billing_status_logs_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: cashflow_entries cashflow_entries_category_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cashflow_entries
    ADD CONSTRAINT cashflow_entries_category_id_foreign FOREIGN KEY (category_id) REFERENCES public.cashflow_categories(id) ON DELETE RESTRICT;


--
-- Name: cashflow_entries cashflow_entries_confirmed_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cashflow_entries
    ADD CONSTRAINT cashflow_entries_confirmed_by_foreign FOREIGN KEY (confirmed_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: cashflow_entries cashflow_entries_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cashflow_entries
    ADD CONSTRAINT cashflow_entries_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: cashflow_entries cashflow_entries_project_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cashflow_entries
    ADD CONSTRAINT cashflow_entries_project_id_foreign FOREIGN KEY (project_id) REFERENCES public.projects(id) ON DELETE SET NULL;


--
-- Name: daily_salaries daily_salaries_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.daily_salaries
    ADD CONSTRAINT daily_salaries_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: daily_salaries daily_salaries_employee_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.daily_salaries
    ADD CONSTRAINT daily_salaries_employee_id_foreign FOREIGN KEY (employee_id) REFERENCES public.employees(id) ON DELETE CASCADE;


--
-- Name: daily_salaries daily_salaries_salary_release_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.daily_salaries
    ADD CONSTRAINT daily_salaries_salary_release_id_foreign FOREIGN KEY (salary_release_id) REFERENCES public.salary_releases(id) ON DELETE SET NULL;


--
-- Name: employee_custom_off_days employee_custom_off_days_employee_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.employee_custom_off_days
    ADD CONSTRAINT employee_custom_off_days_employee_id_foreign FOREIGN KEY (employee_id) REFERENCES public.employees(id) ON DELETE CASCADE;


--
-- Name: employee_work_schedules employee_work_schedules_employee_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.employee_work_schedules
    ADD CONSTRAINT employee_work_schedules_employee_id_foreign FOREIGN KEY (employee_id) REFERENCES public.employees(id) ON DELETE CASCADE;


--
-- Name: expense_approvals expense_approvals_approver_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.expense_approvals
    ADD CONSTRAINT expense_approvals_approver_id_foreign FOREIGN KEY (approver_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: expense_approvals expense_approvals_expense_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.expense_approvals
    ADD CONSTRAINT expense_approvals_expense_id_foreign FOREIGN KEY (expense_id) REFERENCES public.project_expenses(id) ON DELETE CASCADE;


--
-- Name: expense_modification_approvals expense_modification_approvals_approved_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.expense_modification_approvals
    ADD CONSTRAINT expense_modification_approvals_approved_by_foreign FOREIGN KEY (approved_by) REFERENCES public.users(id);


--
-- Name: expense_modification_approvals expense_modification_approvals_expense_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.expense_modification_approvals
    ADD CONSTRAINT expense_modification_approvals_expense_id_foreign FOREIGN KEY (expense_id) REFERENCES public.project_expenses(id) ON DELETE CASCADE;


--
-- Name: expense_modification_approvals expense_modification_approvals_requested_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.expense_modification_approvals
    ADD CONSTRAINT expense_modification_approvals_requested_by_foreign FOREIGN KEY (requested_by) REFERENCES public.users(id);


--
-- Name: import_logs import_logs_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.import_logs
    ADD CONSTRAINT import_logs_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: project_activities project_activities_project_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_activities
    ADD CONSTRAINT project_activities_project_id_foreign FOREIGN KEY (project_id) REFERENCES public.projects(id) ON DELETE CASCADE;


--
-- Name: project_activities project_activities_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_activities
    ADD CONSTRAINT project_activities_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: project_billings project_billings_billing_batch_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_billings
    ADD CONSTRAINT project_billings_billing_batch_id_foreign FOREIGN KEY (billing_batch_id) REFERENCES public.billing_batches(id) ON DELETE SET NULL;


--
-- Name: project_billings project_billings_parent_schedule_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_billings
    ADD CONSTRAINT project_billings_parent_schedule_id_foreign FOREIGN KEY (parent_schedule_id) REFERENCES public.project_payment_schedules(id) ON DELETE CASCADE;


--
-- Name: project_billings project_billings_project_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_billings
    ADD CONSTRAINT project_billings_project_id_foreign FOREIGN KEY (project_id) REFERENCES public.projects(id) ON DELETE CASCADE;


--
-- Name: project_documents project_documents_project_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_documents
    ADD CONSTRAINT project_documents_project_id_foreign FOREIGN KEY (project_id) REFERENCES public.projects(id) ON DELETE CASCADE;


--
-- Name: project_documents project_documents_uploaded_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_documents
    ADD CONSTRAINT project_documents_uploaded_by_foreign FOREIGN KEY (uploaded_by) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: project_expenses project_expenses_project_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_expenses
    ADD CONSTRAINT project_expenses_project_id_foreign FOREIGN KEY (project_id) REFERENCES public.projects(id) ON DELETE CASCADE;


--
-- Name: project_expenses project_expenses_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_expenses
    ADD CONSTRAINT project_expenses_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: project_folders project_folders_parent_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_folders
    ADD CONSTRAINT project_folders_parent_id_foreign FOREIGN KEY (parent_id) REFERENCES public.project_folders(id) ON DELETE CASCADE;


--
-- Name: project_folders project_folders_project_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_folders
    ADD CONSTRAINT project_folders_project_id_foreign FOREIGN KEY (project_id) REFERENCES public.projects(id) ON DELETE CASCADE;


--
-- Name: project_payment_schedules project_payment_schedules_billing_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_payment_schedules
    ADD CONSTRAINT project_payment_schedules_billing_id_foreign FOREIGN KEY (billing_id) REFERENCES public.project_billings(id) ON DELETE SET NULL;


--
-- Name: project_payment_schedules project_payment_schedules_project_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_payment_schedules
    ADD CONSTRAINT project_payment_schedules_project_id_foreign FOREIGN KEY (project_id) REFERENCES public.projects(id) ON DELETE CASCADE;


--
-- Name: project_profit_analyses project_profit_analyses_project_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_profit_analyses
    ADD CONSTRAINT project_profit_analyses_project_id_foreign FOREIGN KEY (project_id) REFERENCES public.projects(id) ON DELETE CASCADE;


--
-- Name: project_revenues project_revenues_project_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_revenues
    ADD CONSTRAINT project_revenues_project_id_foreign FOREIGN KEY (project_id) REFERENCES public.projects(id) ON DELETE CASCADE;


--
-- Name: project_timelines project_timelines_project_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_timelines
    ADD CONSTRAINT project_timelines_project_id_foreign FOREIGN KEY (project_id) REFERENCES public.projects(id) ON DELETE CASCADE;


--
-- Name: revenue_items revenue_items_project_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.revenue_items
    ADD CONSTRAINT revenue_items_project_id_foreign FOREIGN KEY (project_id) REFERENCES public.projects(id) ON DELETE CASCADE;


--
-- Name: revenue_items revenue_items_revenue_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.revenue_items
    ADD CONSTRAINT revenue_items_revenue_id_foreign FOREIGN KEY (revenue_id) REFERENCES public.project_revenues(id) ON DELETE CASCADE;


--
-- Name: role_users role_users_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_users
    ADD CONSTRAINT role_users_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- Name: role_users role_users_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_users
    ADD CONSTRAINT role_users_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: salary_releases salary_releases_cashflow_entry_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.salary_releases
    ADD CONSTRAINT salary_releases_cashflow_entry_id_foreign FOREIGN KEY (cashflow_entry_id) REFERENCES public.cashflow_entries(id) ON DELETE SET NULL;


--
-- Name: salary_releases salary_releases_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.salary_releases
    ADD CONSTRAINT salary_releases_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: salary_releases salary_releases_employee_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.salary_releases
    ADD CONSTRAINT salary_releases_employee_id_foreign FOREIGN KEY (employee_id) REFERENCES public.employees(id) ON DELETE CASCADE;


--
-- Name: salary_releases salary_releases_released_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.salary_releases
    ADD CONSTRAINT salary_releases_released_by_foreign FOREIGN KEY (released_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- PostgreSQL database dump complete
--

