# Database ER Diagram — SmartHire Pro

```mermaid
erDiagram
    roles ||--o{ users : has
    users ||--o| job_seeker_profiles : owns
    users ||--o| companies : manages
    companies ||--o{ jobs : posts
    users ||--o{ applications : submits
    jobs ||--o{ applications : receives
    users ||--o{ saved_jobs : saves
    jobs ||--o{ saved_jobs : saved_in
    users ||--o{ notifications : receives
    users ||--o{ otp_verifications : verifies
    users ||--o{ activity_logs : generates
    users ||--o{ admin_logs : performs
```

## Table Relationships

| Parent | Child | Relationship |
|--------|-------|--------------|
| roles | users | One role, many users |
| users | job_seeker_profiles | One-to-one (job seekers) |
| users | companies | One recruiter, one company |
| companies | jobs | One company, many jobs |
| jobs + users | applications | Many-to-many via applications |
| users + jobs | saved_jobs | Many-to-many via saved_jobs |

See `database/schema.sql` for full column definitions and constraints.
