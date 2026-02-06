<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report Card - {{ $student->full_name }}</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { font-family: 'Times New Roman', Times, serif; color: #333; }
        .report-card { border: 10px double #dc3545; padding: 40px; margin: 20px auto; max-width: 900px; position: relative; }
        .school-logo { width: 100px; height: 100px; margin-bottom: 20px; }
        .header-section { border-bottom: 2px solid #dc3545; margin-bottom: 30px; padding-bottom: 20px; }
        .watermark { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); font-size: 80px; color: rgba(220, 53, 69, 0.05); pointer-events: none; white-space: nowrap; font-weight: bold; }
        .student-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .info-item { border-bottom: 1px dotted #ccc; padding: 5px 0; }
        .info-label { font-weight: bold; text-transform: uppercase; font-size: 12px; color: #666; width: 140px; display: inline-block; }
        .results-table th { background-color: #dc3545; color: white; text-transform: uppercase; font-size: 13px; text-align: center; }
        .results-table td { text-align: center; vertical-align: middle; }
        .results-table td:first-child { text-align: left; padding-left: 15px; font-weight: bold; }
        .stats-box { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 20px; border-left: 5px solid #dc3545; }
        .remarks-section { margin-top: 40px; border-top: 1px solid #eee; padding-top: 20px; }
        .signature-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 40px; margin-top: 80px; text-align: center; }
        .signature-line { border-top: 2px solid #333; margin-top: 50px; font-weight: bold; padding-top: 10px; }
        @media print {
            .btn-print { display: none; }
            .report-card { border: none; padding: 0; margin: 0; }
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="text-right mb-4">
            <button onclick="window.print()" class="btn btn-primary btn-print px-4 elevation-2">
                <i class="fas fa-print mr-2"></i> Print Report Card
            </button>
        </div>

        <div class="report-card bg-white shadow-lg">
            <div class="watermark">OFFICIAL REPORT</div>
            
            <div class="header-section text-center">
                <img src="https://ui-avatars.com/api/?name=LS&background=dc3545&color=fff&size=200&bold=true" class="school-logo rounded-circle mb-3 border p-1" alt="Logo">
                <h1 class="font-weight-bold" style="color: #dc3545; letter-spacing: 2px;">LAVENDER SECONDARY SCHOOL</h1>
                <p class="mb-1 text-muted">P.O. Box 12345 - 00100, Nairobi, Kenya</p>
                <p class="mb-0 text-muted">Email: info@lavender.sc.ke | Web: www.lavender.sc.ke</p>
                <h2 class="mt-4 font-weight-bold text-uppercase" style="background: #333; color: white; display: inline-block; padding: 5px 30px;">Student Progress Report</h2>
            </div>

            <div class="student-info-grid">
                <div>
                    <div class="info-item"><span class="info-label">Student Name:</span> <span class="text-uppercase font-weight-bold">{{ $student->full_name }}</span></div>
                    <div class="info-item"><span class="info-label">Admission No:</span> <b>{{ $student->admission_no }}</b></div>
                    <div class="info-item"><span class="info-label">Class / Form:</span> Form 3 - East</div>
                </div>
                <div>
                    <div class="info-item"><span class="info-label">Exam Session:</span> <b>{{ $exam->name }}</b></div>
                    <div class="info-item"><span class="info-label">Academic Year:</span> 2026</div>
                    <div class="info-item"><span class="info-label">Report Date:</span> {{ date('d M, Y') }}</div>
                </div>
            </div>

            <table class="table table-bordered results-table">
                <thead>
                    <tr>
                        <th width="40%">Learning Area / Subject</th>
                        <th>Marks (%)</th>
                        <th>Grade</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @php $sum = 0; @endphp
                    @foreach($results as $res)
                        @php $sum += $res->marks_obtained; @endphp
                        <tr>
                            <td>{{ $res->subject->name }}</td>
                            <td>{{ number_format($res->marks_obtained, 0) }}</td>
                            <td><b class="text-danger">{{ $res->grade->name ?? '-' }}</b></td>
                            <td class="small font-italic text-muted">Exceeding Expectations</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="stats-box">
                        <h6 class="font-weight-bold mb-3 d-flex justify-content-between">
                            <span>TOTAL MARKS:</span> 
                            <span>{{ number_format($sum, 0) }} / {{ count($results) * 100 }}</span>
                        </h6>
                        <h5 class="font-weight-bold mb-0 d-flex justify-content-between text-danger">
                            <span>MEAN SCORE:</span> 
                            <span>{{ count($results) > 0 ? number_format($sum / count($results), 1) : 0 }}%</span>
                        </h5>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stats-box" style="border-left-color: #28a745;">
                        <h6 class="font-weight-bold mb-3">OVERALL PERFORMANCE</h6>
                        <h5 class="font-weight-bold mb-0 text-success text-center">GRADE: A- (MINUS)</h5>
                    </div>
                </div>
            </div>

            <div class="remarks-section">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <h6 class="font-weight-bold text-uppercase small text-muted">Class Teacher's Remarks:</h6>
                        <p class="border-bottom p-2 font-italic">Good progress shown this term. Continue putting more effort in Mathematics and Physics.</p>
                    </div>
                    <div class="col-md-12">
                        <h6 class="font-weight-bold text-uppercase small text-muted">Principal's Remarks:</h6>
                        <p class="border-bottom p-2 font-italic">A very disciplined and hardworking student. Keep it up!</p>
                    </div>
                </div>
            </div>

            <div class="signature-grid">
                <div>
                    <div class="signature-line">Class Teacher</div>
                </div>
                <div>
                    <img src="https://upload.wikimedia.org/wikipedia/commons/3/3a/Jon_Kirsch_Signature.png" style="height: 50px; opacity: 0.6; margin-bottom: -40px;" alt="Sign">
                    <div class="signature-line">Principal</div>
                </div>
                <div>
                    <div class="signature-line">Parent / Guardian</div>
                </div>
            </div>

            <div class="text-center mt-5">
                <p class="small text-muted mb-0">Lavender School ERP System - Generated on {{ date('d/m/Y H:i') }}</p>
                <p class="small text-muted font-italic">This is a system generated document. Signature of Principal and School Stamp makes it official.</p>
            </div>
        </div>
    </div>
</body>
</html>
