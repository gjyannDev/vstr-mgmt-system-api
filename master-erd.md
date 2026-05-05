// ============================================================
// MASTER ERD — VisitNa (UPDATED EXTENDED)
// ============================================================

// ── GROUP 1: TENANCY & USERS ─────────────────────────────────

TableGroup tenancy {
tenants
locations
users
admin_locations
}

Table tenants {
id char(36) [pk, note: 'UUID']
name varchar(255) [not null]
slug varchar(255)
email varchar(255)
phone varchar(255)
plan varchar(255)
status varchar(255) [not null, default: 'active']
created_at timestamp
updated_at timestamp
}

Table locations {
id char(36) [pk]
tenant_id char(36) [not null, ref: > tenants.id]
name varchar(255) [not null]
type varchar(255)
address_line1 varchar(255)
city varchar(255)
state varchar(255)
created_at timestamp
updated_at timestamp
}

Table users {
id char(36) [pk]
tenant_id char(36) [ref: > tenants.id]
location_id char(36) [ref: > locations.id]
name varchar(255) [not null]
email varchar(255) [unique, not null]
role varchar(255) [not null, default: 'admin']
password varchar(255) [not null]
email_verified_at timestamp
last_login_at timestamp
remember_token varchar(100)
created_at timestamp
updated_at timestamp
}

Table admin_locations {
user_id char(36) [ref: > users.id]
location_id char(36) [ref: > locations.id]
created_at timestamp
updated_at timestamp

indexes {
(user_id, location_id) [pk]
}
}

// ── GROUP 2: VISIT FLOW ───────────────────────────────────────

TableGroup visit_flow {
visitors
hosts
visit_types
visits
}

Table visitors {
id char(36) [pk]
tenant_id char(36) [not null, ref: > tenants.id]
location_id char(36) [not null, ref: > locations.id]
full_name varchar(255) [not null]
email varchar(255)
phone varchar(255)
company varchar(255)

// NEW (optional recurring support)
is_recurring boolean
recurring_qr_id char(36) [ref: > qr_codes.id]

created_at timestamp
updated_at timestamp
}

Table hosts {
id char(36) [pk]
tenant_id char(36) [not null, ref: > tenants.id]
location_id char(36) [not null, ref: > locations.id]
name varchar(255) [not null]
department varchar(255)
email varchar(255)
phone varchar(255)
created_at timestamp
updated_at timestamp
}

Table visit_types {
id char(36) [pk]
tenant_id char(36) [not null, ref: > tenants.id]
location_id char(36) [not null, ref: > locations.id]
name varchar(255) [not null]
description text
requires_approval boolean [default: false]
active boolean [default: true]
is_camera_active boolean [default: false]
created_at timestamp
updated_at timestamp
}

Table visits {
id char(36) [pk]
tenant_id char(36) [not null, ref: > tenants.id]
location_id char(36) [not null, ref: > locations.id]
visitor_id char(36) [not null, ref: > visitors.id]
host_id char(36) [ref: > hosts.id]
visit_type_id char(36) [not null, ref: > visit_types.id]

check_in_by char(36) [ref: > users.id]
check_out_by char(36) [ref: > users.id]

purpose varchar(255)

status varchar(255) [not null, default: 'checked_in']

// NEW (workflow + device)
source varchar(50) // kiosk | web | qr | receptionist
kiosk_id char(36) [ref: > kiosks.id]

// NEW (approval tracking)
approved_at timestamp
approved_by char(36) [ref: > users.id]
rejected_at timestamp
rejection_reason text

session_key char(36)
notes text

check_in_at timestamp
check_out_at timestamp
created_at timestamp
updated_at timestamp
}

// ── GROUP 3: KIOSK ────────────────────────────────────────────

TableGroup kiosk {
kiosks
kiosk_activation_codes
kiosk_visit_types
}

Table kiosks {
id char(36) [pk]
tenant_id char(36) [not null, ref: > tenants.id]
location_id char(36) [not null, ref: > locations.id]
visit_type_id char(36) [ref: > visit_types.id]
name varchar(255) [not null]
status varchar(255) [not null, default: 'active']
last_seen_at timestamp
created_at timestamp
updated_at timestamp
}

Table kiosk_activation_codes {
id char(36) [pk]
kiosk_id char(36) [not null, ref: > kiosks.id]
created_by char(36) [ref: > users.id]
code_hash varchar(64) [unique, not null]
created_ip varchar(45)
expires_at timestamp [not null]
used_at timestamp
created_at timestamp
updated_at timestamp
}

Table kiosk_visit_types {
kiosk_id char(36) [ref: > kiosks.id]
visit_type_id char(36) [ref: > visit_types.id]

indexes {
(kiosk_id, visit_type_id) [pk]
}
}

// ── GROUP 4: FORMS ────────────────────────────────────────────

TableGroup forms {
form_fields
visit_responses
}

Table form_fields {
id char(36) [pk]
tenant_id char(36) [not null, ref: > tenants.id]
location_id char(36) [not null, ref: > locations.id]
visit_type_id char(36) [not null, ref: > visit_types.id]
label varchar(255) [not null]
name varchar(255) [not null]
type varchar(255) [not null]
required boolean [default: false]
options json
validation_rules json
placeholder varchar(255)
is_system boolean [default: false]
sort_order int [default: 0]
created_at timestamp
updated_at timestamp
}

Table visit_responses {
id char(36) [pk]
visit_id char(36) [not null, ref: > visits.id]
form_field_id char(36) [not null, ref: > form_fields.id]
location_id char(36) [not null, ref: > locations.id]
value text
created_at timestamp
updated_at timestamp
}

// ── GROUP 5: EXTENSIONS (NEW FEATURES) ────────────────────────

TableGroup extensions {
visit_type_steps
visit_assets
qr_codes
visit_invitations
badges
location_branding
}

Table visit_type_steps {
id char(36) [pk]
visit_type_id char(36) [ref: > visit_types.id]
step_key varchar(50) // photo | form | signature | approval | badge | qr
step_label varchar(255)
step_order int
config_json json
is_required boolean
created_at timestamp
}

Table visit_assets {
id char(36) [pk]
visit_id char(36) [ref: > visits.id]

type varchar(50) // photo | signature | document
file_url varchar(255)

created_at timestamp
}

Table qr_codes {
id char(36) [pk]
tenant_id char(36) [ref: > tenants.id]
location_id char(36) [ref: > locations.id]

type varchar(50) // visit | recurring | invitation
code varchar(255) [unique]

visit_id char(36) [ref: > visits.id]
visitor_id char(36) [ref: > visitors.id]

expires_at timestamp
revoked_at timestamp

created_at timestamp
}

Table visit_invitations {
id char(36) [pk]
host_id char(36) [ref: > hosts.id]

visitor_name varchar(255)
visitor_email varchar(255)

visit_type_id char(36) [ref: > visit_types.id]
scheduled_at timestamp

qr_code_id char(36) [ref: > qr_codes.id]

status varchar(50)

created_at timestamp
}

Table badges {
id char(36) [pk]
tenant_id char(36) [ref: > tenants.id]
location_id char(36) [ref: > locations.id]

name varchar(255)
config_json json
is_active boolean

created_at timestamp
}

Table location_branding {
id char(36) [pk]
location_id char(36) [ref: > locations.id]

logo_url varchar(255)
primary_color varchar(50)
secondary_color varchar(50)
background_url varchar(255)

welcome_text text
config_json json

created_at timestamp
}

// ── GROUP 6: SYSTEM ──────────────────────────────────────────

TableGroup system {
personal_access_tokens
password_reset_tokens
jobs
failed_jobs
sessions
}

Table personal_access_tokens {
id bigint [pk, increment]
tokenable_type varchar(255)
tokenable_id char(36)
name text
token varchar(64)
abilities text
last_used_at timestamp
expires_at timestamp
created_at timestamp
updated_at timestamp
}

Table password_reset_tokens {
email varchar(255) [pk]
token varchar(255)
created_at timestamp
}

Table jobs {
id bigint [pk, increment]
queue varchar(255)
payload text
attempts int
reserved_at int
available_at int
created_at int
}

Table failed_jobs {
id bigint [pk, increment]
uuid varchar(255)
connection text
queue text
payload text
exception text
failed_at timestamp
}

Table sessions {
id varchar(255) [pk]
user_id char(36)
ip_address varchar(45)
user_agent text
payload text
last_activity int
}
