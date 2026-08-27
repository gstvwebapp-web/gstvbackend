 <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f6f6f6;
            margin: 0;
            padding: 20px;
        }
        .email-container {
            background: #ffffff;
            border-radius: 8px;
            max-width: 600px;
            margin: 0 auto;
            padding: 25px;
            box-shadow: 0px 2px 8px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #ff0000;
            color: #ffffff;
            padding: 15px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .header h2 {
            margin: 0;
            font-size: 20px;
        }
        .content {
            padding: 20px;
            color: #333;
        }
        .content p {
            margin-bottom: 10px;
            line-height: 1.5;
        }
        .footer {
            font-size: 13px;
            text-align: center;
            margin-top: 20px;
            color: #888;
        }
        .highlight {
            color: #ff0000;
            font-weight: bold;
        }
    </style>
<div class="email-container">
        <div class="header">
            <h2>Career Application</h2>
        </div>
        <div class="content">
            <p><strong>Full Name:</strong> {{ $data['fullname'] }}</p>
            <p><strong>Email:</strong> {{ $data['email'] }}</p>
            <p><strong>Phone:</strong> {{ $data['phone'] }}</p>
            <p><strong>Post Applied For:</strong> {{ $data['post'] }}</p>
            <p><strong>Experience:</strong> {{ $data['experience'] }}</p>
            <p><strong>Message:</strong></p>
            <p>{{ $data['message'] }}</p>

            <p>📎 The candidate’s CV is attached with this email.</p>
        </div>
    </div>