<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
</head>

<body>
    <p>{{ $order->buyer->name }} さんが商品 "{{ $order->product->title }}" の取引を完了しました。</p>
    <p>取引画面にてお取引相手の評価を送信してください。</p>
</body>

</html>