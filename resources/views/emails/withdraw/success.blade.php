<!DOCTYPE html>
<html>
<head>
    <title></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="utf-8"/>
    <style type="text/css">
        * {
            padding: 0;
            margin: 0;
        }

        .result_center {
            margin: 0 auto;
            width: 800px;
            padding-top: 3rem;
        }

        .result_content {
            text-align: center;
        }

        .result_title {
            font-size: 16px;
            padding: 20px 0 10px 0;
            font-weight: 600;
        }

        .result_value {
            font-size: 15px;
            padding: 5px 0;
        }

        .result_value span {
            color: #ea9518;
            font-size: 25px;
        }

        .code_info {
            font-weight: 600;
            padding: 5px 0 15px 0;
            font-size: 20px;
        }

        .result_p {
            font-size: 13px;
            color: #666;
        }

        .icon_s {
            text-align: center;
        }

        .dao_text {
            padding: 10px 0;
            color: #888;
            font-size: 13px;
        }

        .footer_result {
            border-top: 1px solid #eee;
            margin-top: 5rem;
            padding-top: 1.3rem;
            font-size: 13px;
            color: #888;
            line-height: 20px;
        }
    </style>
</head>
<body>
<div class="result_center">
    <div class="result_content">
        <div class="icon_s">
            <svg t="1676471101775" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg"
                 p-id="2750" width="128" height="128">
                <path
                    d="M512 0C230.4 0 0 230.4 0 512s230.4 512 512 512c281.6 0 512-230.4 512-512S793.6 0 512 0zM838.4 371.2l-384 384C448 761.6 428.8 768 416 768c-12.8 0-25.6-6.4-38.4-12.8L147.2 524.8C128 505.6 128 467.2 147.2 448c19.2-19.2 57.6-19.2 76.8 0l192 192 345.6-345.6c19.2-19.2 57.6-19.2 76.8 0C864 313.6 864 345.6 838.4 371.2z"
                    p-id="2751" fill="#1296db"></path>
            </svg>
        </div>
        <div class="result_title">你正申请提币</div>
        <div class="result_value"><span>{{$asset->balance}}</span> USDC ($<span>{{ $usdPrice }}</span> USD)</div>
        <div class="dao_text">到</div>
        <div class="code_info">{{ $user->address }}</div>
        <div class="result_p">
            <p>通过以太坊</p>
            <p>如果此操作不是你本人，请立即锁定账号</p>
        </div>
    </div>
    <div class="footer_result">此款项将在T+1内到达，即{{ now()->addDay()->toDateTimeString() }}
        前到达上述地址，在此期间，请确保你的钱包余额有{{ $asset->balance }}USDC提供验证（VIP3以上级别无验证），否则将出现出款失败的情况。
    </div>
</div>
</body>
</html>
