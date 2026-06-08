<?php

function seedDemoData(PDO $db): array
{
    $messages = [];

    $stmt = $db->query(
        "SELECT COUNT(*) FROM jobs j
         JOIN companies c ON c.id = j.company_id
         WHERE c.approval_status = 'approved' AND j.status = 'active'"
    );
    if ((int) $stmt->fetchColumn() > 0) {
        return ['Demo jobs already exist. Skipping seed.'];
    }

    $roleId = (int) $db->query("SELECT id FROM roles WHERE slug = 'recruiter'")->fetchColumn();

    $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute(['demo.recruiter@smarthire.pro']);
    $recruiterId = (int) ($stmt->fetchColumn() ?: 0);

    if (!$recruiterId) {
        $hash = password_hash('Recruiter@123', PASSWORD_BCRYPT);
        $db->prepare(
            'INSERT INTO users (role_id, email, password, full_name, status, email_verified) VALUES (?, ?, ?, ?, ?, 1)'
        )->execute([$roleId, 'demo.recruiter@smarthire.pro', $hash, 'Demo Recruiter', 'active']);
        $recruiterId = (int) $db->lastInsertId();
        $messages[] = 'Demo recruiter: demo.recruiter@smarthire.pro / Recruiter@123';
    }

    $companies = [
        ['TechNova Solutions', 'Leading software company building enterprise web and cloud products.', 'https://technova.example.com', 'Bangalore, India'],
        ['FinEdge Corporation', 'Fast-growing fintech firm focused on digital banking and analytics.', 'https://finedge.example.com', 'Mumbai, India'],
        ['GreenLeaf Healthcare', 'Healthcare technology provider improving patient care through innovation.', 'https://greenleaf.example.com', 'Chennai, India'],
        ['Creative Studio Labs', 'Award-winning digital agency for brands, UI/UX, and marketing campaigns.', 'https://creativestudio.example.com', 'Delhi, India'],
        ['AppWorks Mobile', 'Mobile app studio specializing in Android, iOS, and cross-platform apps.', 'https://appworks.example.com', 'Hyderabad, India'],
    ];

    $companyIds = [];
    $ins = $db->prepare(
        'INSERT INTO companies (recruiter_id, name, description, website, location, approval_status) VALUES (?, ?, ?, ?, ?, ?)'
    );
    foreach ($companies as $c) {
        $ins->execute([$recruiterId, $c[0], $c[1], $c[2], $c[3], 'approved']);
        $companyIds[] = (int) $db->lastInsertId();
    }

    $jobs = [
        [$companyIds[0], 'Senior PHP Developer', 'Build scalable recruitment and SaaS platforms using PHP 8, MySQL, and REST APIs. Collaborate with frontend team on product features.', 'PHP, MySQL, Laravel, REST API, Git', 'Bangalore, India', 'full-time', 'Mid', 900000, 1400000],
        [$companyIds[0], 'React Frontend Developer', 'Develop responsive dashboards and job portals with React, Bootstrap, and modern JavaScript patterns.', 'React, JavaScript, HTML, CSS, API integration', 'Remote', 'remote', 'Mid', 700000, 1100000],
        [$companyIds[0], 'DevOps Engineer', 'Manage CI/CD pipelines, Docker deployments, and cloud infrastructure for production applications.', 'AWS, Docker, CI/CD, Linux, Nginx', 'Bangalore, India', 'full-time', 'Senior', 1200000, 1800000],
        [$companyIds[1], 'Data Analyst Intern', 'Analyze hiring trends, build reports, and support business decisions using SQL and Excel.', 'SQL, Excel, Power BI, Data visualization', 'Mumbai, India', 'internship', 'Entry', 25000, 35000],
        [$companyIds[1], 'Business Analyst', 'Gather requirements, document workflows, and bridge business and engineering teams.', 'Requirements analysis, Jira, Documentation', 'Pune, India', 'full-time', 'Mid', 600000, 900000],
        [$companyIds[1], 'Sales Manager', 'Drive B2B sales, manage client relationships, and achieve quarterly revenue targets.', 'B2B Sales, CRM, Negotiation, Leadership', 'Mumbai, India', 'full-time', 'Senior', 800000, 1500000],
        [$companyIds[2], 'HR Executive', 'Handle end-to-end recruitment, onboarding, and employee engagement initiatives.', 'Recruitment, HR operations, Communication', 'Chennai, India', 'full-time', 'Entry', 350000, 500000],
        [$companyIds[2], 'Nurse Coordinator', 'Coordinate patient schedules, support clinical staff, and maintain healthcare records.', 'Healthcare, Patient care, Scheduling', 'Chennai, India', 'full-time', 'Mid', 400000, 550000],
        [$companyIds[3], 'UI/UX Designer', 'Design intuitive user interfaces for web and mobile apps. Create wireframes and prototypes in Figma.', 'Figma, UI/UX, Wireframing, Design systems', 'Remote', 'contract', 'Senior', 500000, 800000],
        [$companyIds[3], 'Digital Marketing Specialist', 'Run SEO, social media, and paid campaigns to grow brand visibility and leads.', 'SEO, Google Ads, Social media, Analytics', 'Delhi, India', 'full-time', 'Mid', 450000, 700000],
        [$companyIds[3], 'Content Writer', 'Write blog posts, job descriptions, and marketing copy for tech and recruitment brands.', 'Content writing, SEO, Copywriting', 'Remote', 'part-time', 'Entry', 200000, 350000],
        [$companyIds[4], 'Android Developer', 'Build and maintain Android applications with Kotlin and modern architecture patterns.', 'Kotlin, Android SDK, Firebase, Git', 'Hyderabad, India', 'full-time', 'Mid', 800000, 1200000],
        [$companyIds[4], 'Flutter Developer', 'Develop cross-platform mobile apps using Flutter and integrate REST APIs.', 'Flutter, Dart, REST API, Mobile UI', 'Remote', 'remote', 'Mid', 750000, 1150000],
        [$companyIds[0], 'Junior Web Developer', 'Perfect for fresh graduates. Learn PHP, MySQL, and frontend basics while building real projects.', 'HTML, CSS, JavaScript, PHP basics', 'Bangalore, India', 'full-time', 'Entry', 300000, 450000],
        [$companyIds[2], 'Customer Support Associate', 'Respond to customer queries via email and chat. Weekend shifts may apply.', 'Communication, Customer service, Email support', 'Chennai, India', 'part-time', 'Entry', 180000, 240000],
    ];

    $jobIns = $db->prepare(
        'INSERT INTO jobs (company_id, posted_by, title, description, requirements, location, job_type, experience_level, salary_min, salary_max, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    foreach ($jobs as $j) {
        $jobIns->execute([$j[0], $recruiterId, $j[1], $j[2], $j[3], $j[4], $j[5], $j[6], $j[7], $j[8], 'active']);
    }

    $messages[] = count($jobs) . ' demo jobs added across 5 companies.';
    return $messages;
}
