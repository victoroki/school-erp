php # 🎯 HR MODULE - USER GUIDE

## Quick Start

### Accessing the HR Module

1. **HR Dashboard**: Navigate to `/hr/dashboard` or click "HR Dashboard" in the sidebar
2. **Main Features**: Access all HR features from the sidebar under "Human Resources"

---

## 📋 FEATURES OVERVIEW

### 1. HR DASHBOARD (`/hr/dashboard`)

**What You'll See:**
- **Summary Cards**: Total staff, active staff, on leave today, pending requests, vacancies, contracts expiring, monthly payroll
- **Staff by Department**: Breakdown of staff count per department
- **Staff on Leave Today**: List of staff currently on leave
- **Recent Hires**: New staff joined this month
- **Upcoming Birthdays**: Birthdays in the next 7 days
- **Alerts**: Documents expiring, probation ending, contracts expiring, vacant positions
- **Quick Actions**: Add staff, approve leaves, process payroll, view reports

---

### 2. LEAVE MANAGEMENT

#### Apply for Leave (`/leave-applications/create`)
1. Select leave type (Annual, Sick, Maternity, etc.)
2. Choose start and end dates
3. Enter reason for leave
4. Optionally assign relief staff
5. Upload supporting documents (required for sick leave > 3 days)
6. Submit application

**Validation:**
- ✅ Checks leave balance before submission
- ✅ Validates advance notice requirement
- ✅ Calculates working days (excludes weekends)
- ✅ Requires medical certificate for sick leave > 3 days

#### Approval Workflow
**Two-Level Approval:**
1. **HOD Approval**: Head of Department reviews first
2. **HR Approval**: HR Manager gives final approval

**Status Indicators:**
- 🟡 Pending - Awaiting approval
- 🟢 Approved - Both HOD and HR approved
- 🔴 Rejected - Application denied

#### View Leave Applications (`/leave-applications`)
- Filter by status, leave type, staff
- See approval status (HOD and HR)
- Approve/reject applications
- View detailed leave information

---

### 3. ATTENDANCE MANAGEMENT

#### Mark Daily Attendance (`/staff-attendance/create`)
1. Select date
2. Mark each staff member as:
   - ✅ Present
   - ❌ Absent
   - ⏰ Late
   - 🕐 Half Day
   - 📅 On Leave
3. Enter time in/out (optional)
4. Add notes if needed
5. Save attendance

**Quick Actions:**
- Mark all present (one click)
- Mark all absent (one click)

#### View Attendance (`/staff-attendance`)
- **Summary Cards**: Total staff, present, absent, late, on leave
- **Filter by**: Date, department
- **Edit**: Modify attendance records
- **Reports**: Daily attendance trends

---

### 4. PAYROLL PROCESSING

#### Process Monthly Payroll (`/payroll-processing`)

**Step 1: Select Period**
- Choose month and year
- Click "Calculate Payroll"

**Step 2: Review Calculations**
The system automatically calculates:
- **Basic Salary**: From staff record
- **Allowances**: Housing, transport, medical, etc.
- **Gross Salary**: Basic + Allowances
- **PAYE**: Kenya tax (2024 rates with personal relief)
- **NHIF**: Tiered contributions based on salary
- **NSSF**: Tier I & II (6% each)
- **Other Deductions**: Loans, advances, etc.
- **Net Salary**: Gross - Total Deductions

**Step 3: Approve & Process**
- Review the detailed breakdown
- Verify totals
- Click "Approve & Process Payroll"

**Kenya Tax Calculations:**
```
PAYE Bands (2024):
- Up to 24,000: 10%
- 24,001 - 32,333: 25%
- 32,334 - 500,000: 30%
- 500,001 - 800,000: 32.5%
- Above 800,000: 35%
Personal Relief: KES 2,400

NHIF: Tiered from KES 150 to KES 1,700
NSSF: 6% Tier I (up to 7,000) + 6% Tier II (7,001 - 36,000)
```

---

### 5. STAFF ONBOARDING

#### View Onboarding Progress (`/hr/onboarding`)
- See all staff in onboarding process
- Progress bar shows completion percentage
- Click "View Checklist" to see details

**Default Checklist Items:**
1. ✅ Complete employment contract
2. ✅ Submit required documents
3. ✅ Create user account
4. ✅ Issue ID card
5. ✅ Setup email account
6. ✅ Assign to department
7. ✅ Workspace setup
8. ✅ IT equipment allocation
9. ✅ Introduction to team
10. ✅ HR orientation session
11. ✅ Safety & security briefing
12. ✅ Add to payroll system

**Mark Items Complete:**
- Click on each item to mark as completed
- Progress updates automatically
- Completion date recorded

---

### 6. EXIT MANAGEMENT

#### Initiate Exit Process (`/hr/exit/create`)
1. Select staff member
2. Choose exit type:
   - Resignation
   - Termination
   - Retirement
   - Contract End
3. Enter exit date
4. Provide reason
5. Specify notice period
6. Submit

**What Happens:**
- ✅ Staff status updated to "Resigned" or "Terminated"
- ✅ Exit clearance record created
- ✅ User account automatically deactivated
- ✅ Final settlement calculation initiated

#### View Exiting Staff (`/hr/exit`)
- See all staff in exit process
- Track clearance status
- Monitor final settlements

---

### 7. HR REPORTS

#### Headcount Report (`/hr/reports/headcount`)
**Breakdowns:**
- Staff by Department
- By Employment Type (Full-time, Part-time, Contract, etc.)
- Gender Distribution
- Teaching vs Non-Teaching
- Age Distribution

#### Leave Analytics (`/hr/reports/leave`)
**Metrics:**
- Leave applications by type
- Approval/rejection rates
- Total days taken
- Monthly trends
- Pending applications

#### Payroll Analytics (`/hr/reports/payroll`)
**Insights:**
- Total payroll cost
- Cost by department
- Average salary by department
- Staff count per department
- Monthly trends

#### Attendance Reports (`/hr/reports/attendance`)
**Data:**
- Daily attendance summary
- Present/absent/late counts
- Monthly trends
- Department-wise breakdown

---

## 🔐 PERMISSIONS & ROLES

### HR Manager
- ✅ Full access to all HR features
- ✅ Approve/reject leave applications
- ✅ Process payroll
- ✅ Manage staff records
- ✅ View all reports

### Department Head (HOD)
- ✅ View department staff
- ✅ Approve leave (first level)
- ✅ Mark attendance
- ✅ View department reports

### Staff (Self-Service)
- ✅ Apply for leave
- ✅ View own leave balance
- ✅ View own attendance
- ✅ View own payslips
- ✅ Update personal information

---

## 💡 TIPS & BEST PRACTICES

### Leave Management
1. **Apply Early**: Submit leave applications at least 7 days in advance
2. **Check Balance**: Always verify leave balance before applying
3. **Medical Certificate**: Required for sick leave exceeding 3 days
4. **Relief Staff**: Assign someone to cover your duties
5. **Handover Notes**: Provide clear instructions for relief staff

### Attendance
1. **Daily Marking**: Mark attendance every day before noon
2. **Accuracy**: Double-check before saving
3. **Late Arrivals**: Use "Late" status, not "Absent"
4. **Leave Integration**: Staff on approved leave auto-marked

### Payroll
1. **Monthly Schedule**: Process payroll by 25th of each month
2. **Verify Data**: Review all calculations before approval
3. **Allowances**: Update staff allowances before processing
4. **Deductions**: Ensure loan deductions are current
5. **Bank Details**: Verify staff bank accounts are correct

### Onboarding
1. **Start Early**: Begin checklist on day one
2. **Track Progress**: Update checklist daily
3. **Complete All Items**: Don't skip any steps
4. **Documentation**: Collect all required documents
5. **Follow-up**: Check progress weekly

---

## 🚨 COMMON ISSUES & SOLUTIONS

### Leave Application Rejected
**Possible Reasons:**
- Insufficient leave balance
- Inadequate advance notice
- Missing medical certificate
- Overlapping leave periods
- Critical business period

**Solution:** Contact HR for clarification

### Payroll Discrepancy
**Check:**
- Basic salary is correct
- All allowances included
- Deductions are accurate
- Tax calculations (PAYE, NHIF, NSSF)
- Previous month's adjustments

**Action:** Report to HR immediately

### Attendance Not Showing
**Verify:**
- Correct date selected
- Department filter not applied
- Attendance was actually marked
- You have permission to view

**Fix:** Clear filters or contact system admin

---

## 📞 SUPPORT

For HR-related queries:
- **Email**: hr@yourschool.com
- **Phone**: +254 XXX XXX XXX
- **Office**: HR Department, Main Block

For technical issues:
- **Email**: support@yourschool.com
- **Phone**: +254 XXX XXX XXX

---

## 📊 QUICK REFERENCE

### Leave Types
- **Annual Leave**: 21-30 days/year
- **Sick Leave**: As per policy
- **Maternity Leave**: 90 days
- **Paternity Leave**: 14 days
- **Compassionate Leave**: 3-5 days
- **Study Leave**: As approved

### Attendance Status
- **Present**: On time, full day
- **Late**: Arrived after scheduled time
- **Absent**: Did not report
- **Half Day**: Worked partial day
- **On Leave**: Approved leave

### Payroll Components
- **Basic Salary**: Fixed monthly salary
- **House Allowance**: Housing benefit
- **Transport Allowance**: Commute benefit
- **Medical Allowance**: Health benefit
- **PAYE**: Income tax
- **NHIF**: Health insurance
- **NSSF**: Pension contribution
- **Loans**: Salary advances/loans

---

## 🎓 TRAINING RESOURCES

### Video Tutorials
1. How to Apply for Leave
2. Marking Daily Attendance
3. Processing Monthly Payroll
4. Understanding Your Payslip
5. Staff Onboarding Process

### Documentation
- HR Policy Manual
- Leave Policy
- Payroll Guidelines
- Attendance Procedures
- Exit Process Guide

---

**Last Updated**: February 6, 2026
**Version**: 1.0
**Module**: Human Resources Management
