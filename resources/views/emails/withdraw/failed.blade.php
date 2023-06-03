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
            margin-top: 2rem;
            font-size: 16px;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="result_center">
    <div class="result_content">
        <div class="icon_s">
            <svg t="1676471577420" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg"
                 p-id="3869" width="128" height="128">
                <path
                    d="M512 0a512 512 0 1 0 512 512A512 512 0 0 0 512 0z m253.44 693.248l-72.192 72.192L512 584.192l-181.248 181.248-72.192-72.192L439.808 512 258.56 330.752l72.192-72.192L512 439.808l181.248-181.248 72.192 72.192L584.192 512z"
                    fill="#d81e06" p-id="3870"></path>
            </svg>
        </div>
        <div class="result_title">您发起的申请</div>
        <div class="result_value"><span>{{$asset->balance}}</span> USDC ($<span>{{ $usdPrice }}</span> USD)</div>
        <div class="dao_text">到</div>
        <div class="code_info">{{ $user->address }}</div>
        <div class="result_p">
            <p>通过以太坊</p>
            <p>已失败，失败原因：{{ $exception->getMessage() }}</p>
        </div>
    </div>
    <div class="footer_result">如有疑问请联系xxxx@qq.com</div>
</div>
</body>
</html>
