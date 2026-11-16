<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student ID Card - {{ $student->full_name }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        
        body { font-family: 'Inter', sans-serif; background: #f0f2f5; margin: 0; padding: 40px; }
        
        .id-card-container {
            width: 350px;
            height: 500px;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            position: relative;
            margin: 0 auto;
        }
        
        .header {
            height: 120px;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            text-align: center;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .school-name { font-size: 1.2rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; }
        .school-motto { font-size: 0.7rem; opacity: 0.8; font-style: italic; }
        
        .photo-area {
            position: absolute;
            top: 85px;
            left: 50%;
            transform: translateX(-50%);
            width: 140px;
            height: 140px;
            border-radius: 50%;
            border: 6px solid white;
            background: #eee;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        
        .photo-area img { width: 100%; height: 100%; object-fit: cover; }
        .photo-area .initials {
            width: 100%; height: 100%;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 3rem; font-weight: 700;
        }
        
        .details {
            margin-top: 110px;
            text-align: center;
            padding: 0 30px;
        }
        
        .student-name { font-size: 1.4rem; font-weight: 700; color: #1e3c72; margin-bottom: 10px; text-transform: uppercase; }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            margin-top: 25px;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 5px;
        }
        
        .label { font-size: 0.7rem; color: #888; text-transform: uppercase; font-weight: 600; }
        .value { font-size: 0.85rem; color: #333; font-weight: 600; }
        
        .footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            height: 60px;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .barcode {
            width: 80%;
            height: 30px;
            background: repeating-linear-gradient(90deg, #333, #333 1px, transparent 1px, transparent 4px);
            opacity: 0.7;
        }
        
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            background: #1e3c72;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        
        @media print {
            body { background: white; padding: 0; }
            .print-btn { display: none; }
            .id-card-container { box-shadow: none; border: 1px solid #eee; }
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">Print ID Card</button>

    <div class="id-card-container">
        <div class="header">
            <div class="school-name">GARIKON SCHOOLS</div>
            <div class="school-motto">Excellence in Every Child</div>
        </div>
        
        <div class="photo-area">
            @if($student->has_photo && $student->avatar_url)
                <img src="{{ $student->avatar_url }}" alt="{{ $student->full_name }}">
            @else
                <div class="initials" style="background-color: {{ $student->avatar_color }};">{{ $student->initials }}</div>
            @endif
        </div>
        
        <div class="details">
            <div class="student-name">{{ $student->full_name }}</div>
            
            <div class="info-grid">
                <div class="info-item">
                    <span class="label">Admission No</span>
                    <span class="value">{{ $student->admission_no }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Class</span>
                    <span class="value">{{ $student->current_enrollment->classSection->schoolClass->name ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Section</span>
                    <span class="value">{{ $student->current_enrollment->classSection->section->name ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Valid Until</span>
                    <span class="value">December 2026</span>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <div class="barcode"></div>
        </div>
    </div>
</body>
</html>
