<?php
//thông tin tài khoản
//Nếu không get được token vui lòng đăng nhập facebook bằng trình duyệt lại 1 lần và xác nhận IP của server bạn up code là hợp lệ thì sẽ đc!
$user = $_GET['email'];
$pass = $_GET['pass'];

//Thông tin APP facebook
$secretkey = "62f8ce9f74b12f84c123cc23437a4a32";
$api_key = "882a8490361da98702bf97a021ddc14d";


//hàm này sẽ tạo ra biến sig
//Đại khái đây là chuỗn bảo mật của facebook bao gồm thông tin tất cả các biến truyền vào trong $postdate sau đó ghép với $secretkey rồi md5 tất cả là ra
//Ai muốn tìm hiểu đọc tài liệu API của facebook nha :D
function tao_sig($postdata){
global $secretkey;
$textsig = "";
foreach($postdata as $key => $value){
$textsig .= "$key=$value";
}
$textsig .= $secretkey;
$textsig = md5($textsig);

return $textsig;
}

//Hàm curl để post dữ liệu thôi
function getpage($url, $postdata='')
{
$c = curl_init();
curl_setopt($c, CURLOPT_URL, $url);
//2 dòng dưới dùng để bỏ qua xác thực https vì link của facebook là https
curl_setopt($c, CURLOPT_SSL_VERIFYPEER,false);
curl_setopt($c, CURLOPT_SSL_VERIFYHOST,false);
//Cũng không quan trọng chỉ là vẫn curl trang tiếp theo khi header trả về là 1 redirect link 
//Và Trả nội dung về 1 biến chứ không xuất ra màn hình luôn
curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($c, CURLOPT_RETURNTRANSFER, true);

//nhìn là biết he
curl_setopt($c, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:44.0) Gecko/20100101 Firefox/44.0');

//Post data
curl_setopt($c, CURLOPT_POST, 1);
curl_setopt($c, CURLOPT_POSTFIELDS, $postdata);


$page = curl_exec($c);
curl_close($c);
return $page;
}

//mảng chứa các giá sẽ POST lên server facebook
$postdata = array(
"api_key" => $api_key,
"email" => $user,
"format" => "JSON",
"locale" => "vi_vn",
"method" => "auth.login",
"password" => $pass,
"return_ssl_resources" => "0",
"v" => "1.0"
);

//dùng hàm tạo ra chuỗi sig rồi ghép vào mảng chứa các giá trị cần POST
$postdata['sig'] = tao_sig($postdata);

//build nó thành dạng POST data
http_build_query($postdata);

//Curl POST data trên tới setver facebook
$data = getpage("https://api.facebook.com/restserver.php",$postdata);

//Vì facebook sẽ trả về dạng JSON nên dùng hàm này để chuyển thành mảng (array) để dễ truy xuất dữ liệu
$data = json_decode($data);

//lấy token trong mảng mới chuyển ra
$token = $data->access_token;

//show token ra thôi
echo $token;


//phần này là để kiểm tra lại các quyền token thôi
//Không cần thiết thì xóa đi cũng đc :D
echo "<pre>";
print_r(getpage("https://graph.facebook.com/me/permissions?access_token=$token"));
echo "</pre>";
?>
