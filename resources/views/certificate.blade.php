<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tayari Platform - Certificate Juma Kujellah</title>

    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f2f2f2;
            padding: 20px;
        }

        .certificate {
            width: 100%;
            max-width: 100%;
            margin: auto;
            border: 5px solid #e95f38;
            border-radius: 10px;
            overflow: hidden;
            background-color: white;
        }

        .certificate-header {
            background-color: #e95f38;
            color: white;
            height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .certificate-header h1 {
            font-size: 2.5rem;
            font-weight: bold;
        }

        .certificate-body {
            padding: 40px;
        }

        .certificate-body h2 {
            font-size: 1.8rem;
            margin-top: 20px;
        }

        .certificate-body p {
            font-size: 1.1rem;
            margin: 10px 0;
        }

        .certificate-footer {
            margin-top: 30px;
            font-size: 0.9rem;
            color: #555;
        }

        .certificate-id {
            font-weight: bold;
        }

        .verified-link {
            color: #007bff;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="certificate shadow">
        <!-- Header -->
        <div class="certificate-header text-center">
            <div>
                <h1>Certificate of Completion</h1>
            </div>

        </div>
        <div class="certificate-header text-center" style="height: 50px; align-items:flex-start">
            <div>
                <p>This certifies that</p>
            </div>

        </div>



        <!-- Body -->
        <div class="certificate-body text-center">
            <h2>{{ $certificate['user']['first_name'] }} {{ $certificate['user']['last_name'] }}</h2>
            <p>has successfully completed the course</p>
            <h2>{{ $certificate['course']['name'] }}</h2>
            <p>All course requirements completed</p>
            <p><strong>TAYARI</strong></p>
            <p>Professional Skills Development Platform</p>

            <div class="row justify-content-center mt-4">
                <div class="col-md-4 text-start">
                    <p><strong>Date
                            Issued</strong><br>{{ \Carbon\Carbon::parse($certificate['issued_at'])->format('F d, Y') }}
                    </p>
                </div>
                <div class="col-md-4 text-start">
                    <p><strong>Certificate ID</strong><br><span
                            class="certificate-id">{{ $certificate['certificate_code'] }}</span></p>
                </div>
            </div>

            <div class="certificate-footer text-center mt-4">
                <p><strong>Verified Certificate</strong><br>
                    This certificate can be verified at
                    <a class="verified-link"
                        href="https://tayari-skill-up-africa.lovable.app/certificate/{{ $certificate['certificate_code'] }}"
                        target="_blank">
                        https://tayari-skill-up-africa.lovable.app/certificate/{{ $certificate['certificate_code'] }}
                    </a>
                </p>

                <p class="mt-5">This certificate represents the successful completion of
                    {{ $certificate['course']['name'] }}
                    and demonstrates proficiency in the course objectives and learning outcomes.</p>
            </div>
        </div>
    </div>

    <script src="{{ asset('bootstrap.bundle.min.js') }}"></script>

</body>

</html>
