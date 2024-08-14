 <!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Title -->
    <title>Report</title>
</head>
<body>
    <div>
        <table style="width: 100%;">
            <tbody>
                <tr>
                    <td style="width: 18.3944%; border-bottom: 2px solid black;" rowspan="3">
                        <img src="https://pay.somxchange.com/public/uploads/logos/1703528117_logo.png" style="width: 196px;">
                    </td>
                    <td style="width: 46.7026%; border-bottom: 2px solid black;" rowspan="3" colspan="3">
                        <div style="text-align: center;">
                            <strong><span style="text-align: center; color: black; background-color: #f2f2f2; font-size: 30px;">
                                <strong><span style="font-family: Verdana, Geneva, sans-serif;">{{settings('name')}} </span></strong>
                            </span></strong>
                        </div>
                        <header style="text-align: center;">
                            <span style="text-align: center; color: black; background-color: #f2f2f2; font-size: 20px; line-height: 1; font-family: Verdana, Geneva, sans-serif;">Maka Al Mukarama Street, Olow Tower</span>
                            <span style="font-size: 20px;"><br></span>
                            <span style="text-align: center; color: black; background-color: #f2f2f2; font-size: 20px; line-height: 1; font-family: Verdana, Geneva, sans-serif;">Mogadishu - Somalia</span>
                        </header>
                        <p style="text-align: center;">
                            <span style="text-align: center; color: black; background-color: #f2f2f2; font-size: 17px; line-height: 1; font-family: Verdana, Geneva, sans-serif;">{{ $report }}</span>
                        </p>
                    </td>
                    <td style="width: 9.8877%; border-bottom: 2px solid black;"><strong>Date :</strong></td>
                    <td style="width: 14.387%; border-bottom: 2px solid black;">
                        <b><strong style="text-align: left;color: black;background-color: #f2f2f2;font-size: medium;">&nbsp;</strong></b>
                        <span style="text-align: left;color: black;background-color: #f2f2f2;font-size: medium;">{{ $date }}</span>
                    </td>
                </tr>
                <tr>
                    <td style="width: 9.8877%; border-bottom: 2px solid black;"><strong>Time :</strong></td>
                    <td style="width: 14.387%; border-bottom: 2px solid black;">
                        <span style="text-align: left;color: black;background-color: #f2f2f2;font-size: medium;">{{ $time }}</span>
                    </td>
                </tr>
                <tr>
                    <td style="width: 9.8877%; border-bottom: 2px solid black;">
                        <strong>User :</strong>
                    </td>
                    <td style="width: 14.387%; border-bottom: 2px solid black;">
                        <span style="color: black; font-family: 'Times New Roman'; font-size: medium; background-color: #f2f2f2;">{{ $user }}</span>
                    </td>
                </tr> 
            </tbody>
        </table>
        
        <b style="font-size: 14px; font-family: Verdana, Geneva, sans-serif;" >{{ $data }}</b>
</body>
</html> 
