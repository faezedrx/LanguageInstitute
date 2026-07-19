<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<title>محاسبه حقوق مدرس</title>

<style>
body{
    margin:0;
    font-family: "Segoe UI", Tahoma, sans-serif;
    background: linear-gradient(135deg,#dbeafe,#e9d5ff);
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
}
.container{
    width:100%;
    max-width:900px;
    background:#ffffffee;
    border-radius:32px;
    padding:40px;
    box-shadow:0 25px 60px rgba(0,0,0,.15);
}
h1{
    text-align:center;
    color:#4338ca;
    margin-bottom:40px;
}
.inputs{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:20px;
    margin-bottom:40px;
}
.field label{
    display:block;
    margin-bottom:6px;
    font-size:14px;
    color:#555;
    font-weight:600;
}
.field input,.field select{
    width:100%;
    padding:12px 14px;
    border-radius:16px;
    border:1px solid #ccc;
    font-size:15px;
}
.field input:focus,.field select:focus{
    outline:none;
    border-color:#6366f1;
    box-shadow:0 0 0 3px #6366f133;
}
.breakdown{
    display:flex;
    flex-direction:column;
    gap:16px;
}
.row{
    background:#f9fafb;
    border-radius:18px;
    padding:18px 22px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    box-shadow:0 8px 20px rgba(0,0,0,.06);
}
.title{
    font-weight:700;
    color:#374151;
}
.formula{
    font-size:13px;
    color:#9ca3af;
    margin-top:4px;
}
.amount{
    font-size:20px;
    font-weight:800;
    color:#4f46e5;
}
.total{
    margin-top:40px;
    text-align:center;
}
.total-box{
    display:inline-block;
    padding:18px 50px;
    border-radius:22px;
    background:linear-gradient(135deg,#22c55e,#16a34a);
    color:#fff;
    font-size:28px;
    font-weight:900;
    box-shadow:0 15px 35px rgba(0,0,0,.25);
}
@media(max-width:600px){
    h1{font-size:22px}
    .amount{font-size:18px}
    .total-box{font-size:22px}
}
</style>
</head>

<body>

<div class="container">

<h1>💼 داشبورد محاسبه حقوق مدرس</h1>

<div class="inputs">
    <div class="field">
        <label>مبلغ پایه هر جلسه</label>
        <input id="payment" type="number">
    </div>
    <div class="field">
        <label>تعداد جلسات عمومی</label>
        <input id="sessions" type="number">
    </div>
    <div class="field">
        <label>تعداد جلسات خصوصی</label>
        <input id="private_sessions" type="number">
    </div>
    <div class="field">
        <label>سطح خصوصی</label>
        <select id="level">
            <option value="">—</option>
            <option value="tn">TN به بعد</option>
        </select>
    </div>
    <div class="field">
        <label>ساب گرفته</label>
        <input id="sub_taken" type="number">
    </div>
    <div class="field">
        <label>ساب رفته</label>
        <input id="sub_given" type="number">
    </div>
    <div class="field">
        <label>ساعت پشتیبانی</label>
        <input id="support_hours" type="number">
    </div>
</div>

<div class="breakdown">

    <div class="row">
        <div>
            <div class="title">کلاس عمومی</div>
            <div class="formula">جلسات × مبلغ پایه</div>
        </div>
        <div class="amount" id="r_public">0</div>
    </div>

    <div class="row">
        <div>
            <div class="title">کلاس خصوصی</div>
            <div class="formula">جلسات خصوصی × نرخ خصوصی</div>
        </div>
        <div class="amount" id="r_private">0</div>
    </div>

    <div class="row">
        <div>
            <div class="title">ساب گرفته</div>
            <div class="formula">تعداد × (مبلغ ÷ 1.5)</div>
        </div>
        <div class="amount" id="r_sub_taken">0</div>
    </div>

    <div class="row">
        <div>
            <div class="title">ساب رفته</div>
            <div class="formula">تعداد × (مبلغ × 1.5)</div>
        </div>
        <div class="amount" id="r_sub_given">0</div>
    </div>

    <div class="row">
        <div>
            <div class="title">کلاس جبرانی</div>
            <div class="formula">۱ جلسه × مبلغ پایه</div>
        </div>
        <div class="amount" id="r_comp">0</div>
    </div>

    <div class="row">
        <div>
            <div class="title">پشتیبانی</div>
            <div class="formula">ساعت × ۸۰٬۰۰۰</div>
        </div>
        <div class="amount" id="r_support">0</div>
    </div>

</div>

<div class="total">
    <div class="total-box">
        جمع کل: <span id="r_total">0</span> تومان
    </div>
</div>

</div>

<script>
const ids=[
'payment','sessions','private_sessions',
'sub_taken','sub_given','support_hours','level'
];
ids.forEach(id=>document.getElementById(id).addEventListener('input',calc));

function calc(){
    let payment=+v('payment');
    let sessions=+v('sessions');
    let privSessions=+v('private_sessions');
    let level=v('level');
    let subT=+v('sub_taken');
    let subG=+v('sub_given');
    let support=+v('support_hours');

    let pub=payment*sessions;
    let privRate=level==='tn'?0.7*590000:0;
    let priv=privSessions*privRate;
    let st=subT*(payment/1.5);
    let sg=subG*(payment*1.5);
    let comp=payment;
    let sup=support*80000;

    set('r_public',pub);
    set('r_private',priv);
    set('r_sub_taken',st);
    set('r_sub_given',sg);
    set('r_comp',comp);
    set('r_support',sup);
    set('r_total',pub+priv+st+sg+comp+sup);
}
function v(id){return document.getElementById(id).value||0}
function set(id,val){
    document.getElementById(id).innerText=
    Math.round(val).toLocaleString();
}
</script>

</body>
</html>
