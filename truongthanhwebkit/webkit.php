<?php

//Hàm lấy danh sách sản phẩm
function getAllDataProducts($conn)
{
    // Khởi tạo biến kết quả
    $result = array();

    // Câu truy vấn SQL để lấy tất cả dữ liệu từ bảng products
    $query = "SELECT * FROM products";

    // Thực thi truy vấn
    $stmt = $conn->query($query);

    // Kiểm tra và lấy dữ liệu
    if ($stmt) {
        while ($row = $stmt->fetch_assoc()) {
            $result[] = $row; // Thêm từng dòng dữ liệu vào mảng kết quả
        }
    } else {
        // Xử lý lỗi nếu truy vấn thất bại
        $result = array('error' => 'Không thể lấy dữ liệu từ bảng products: ' . $conn->error);
    }

    // Trả về kết quả
    return $result;
}

//Hàm lấy phân trang sản phẩm
function getPaginatedProducts($conn, $page = 1, $perPage = 10)
{
    // Đảm bảo page là số dương
    $page = max(1, (int)$page);

    // Tính offset
    $offset = ($page - 1) * $perPage;

    // Đếm tổng số sản phẩm
    $totalQuery = "SELECT COUNT(*) as total FROM products";
    $totalResult = $conn->query($totalQuery);
    $totalRow = $totalResult->fetch_assoc();
    $totalProducts = $totalRow['total'];

    // Tính tổng số trang
    $totalPages = ceil($totalProducts / $perPage);

    // Truy vấn lấy sản phẩm theo trang
    $query = "SELECT * FROM products LIMIT $perPage OFFSET $offset";
    $stmt = $conn->query($query);

    // Lấy dữ liệu sản phẩm
    $products = array();
    if ($stmt) {
        while ($row = $stmt->fetch_assoc()) {
            $products[] = $row;
        }
    } else {
        $products = array('error' => 'Không thể lấy dữ liệu: ' . $conn->error);
    }

    // Trả về kết quả
    return array(
        'products' => $products,
        'current_page' => $page,
        'total_pages' => $totalPages,
        'per_page' => $perPage,
        'total_products' => $totalProducts
    );
}

//Hàm lấy danh sách danh mục
function getAllCategories($conn)
{
    // Khởi tạo mảng kết quả
    $categories = array();

    // Câu truy vấn SQL để lấy tất cả danh mục
    $query = "SELECT * FROM category";

    // Thực thi truy vấn
    $stmt = $conn->query($query);

    // Kiểm tra và lấy dữ liệu
    if ($stmt) {
        while ($row = $stmt->fetch_assoc()) {
            $categories[] = $row; // Thêm từng danh mục vào mảng
        }
    } else {
        // Xử lý lỗi nếu truy vấn thất bại
        $categories = array('error' => 'Không thể lấy dữ liệu từ bảng category: ' . $conn->error);
    }

    // Trả về kết quả
    return $categories;
}

//Hàm lấy danh sách thương hiệu
function getAllBrands($conn)
{
    // Khởi tạo mảng kết quả
    $brands = array();

    // Câu truy vấn SQL để lấy tất cả thương hiệu
    $query = "SELECT * FROM brands";

    // Thực thi truy vấn
    $stmt = $conn->query($query);

    // Kiểm tra và lấy dữ liệu
    if ($stmt) {
        while ($row = $stmt->fetch_assoc()) {
            $brands[] = $row; // Thêm từng thương hiệu vào mảng
        }
    } else {
        // Xử lý lỗi nếu truy vấn thất bại
        $brands = array('error' => 'Không thể lấy dữ liệu từ bảng brands: ' . $conn->error);
    }

    // Trả về kết quả
    return $brands;
}

//Hàm lấy ra chi tiết sản phẩm
function getProductDetails($conn, $product_id)
{
    // Khởi tạo mảng kết quả
    $product = array();

    // Đảm bảo product_id là số nguyên
    $product_id = (int)$product_id;

    // Câu truy vấn SQL để lấy chi tiết sản phẩm
    $query = "SELECT * FROM products WHERE product_id = $product_id LIMIT 1";

    // Thực thi truy vấn
    $stmt = $conn->query($query);

    // Kiểm tra và lấy dữ liệu
    if ($stmt && $stmt->num_rows > 0) {
        $product = $stmt->fetch_assoc(); // Lấy dòng dữ liệu đầu tiên
    } else {
        // Trả về lỗi nếu không tìm thấy sản phẩm
        $product = array('error' => 'Không tìm thấy sản phẩm với ID: ' . $product_id);
    }

    // Trả về kết quả
    return $product;
}

//Hàm lấy sản phẩm liên quan
function getRelatedProductsByCategory($conn, $categoryID, $limit)
{
    // Truy vấn để lấy các sản phẩm liên quan theo CategoryID
    $sql = "SELECT * FROM products WHERE CategoryID = ? LIMIT ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $categoryID, $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $relatedProducts = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $relatedProducts[] = $row;
    }

    mysqli_stmt_close($stmt);
    return $relatedProducts;
}

//Hàm lấy danh sách sản phẩm từ id danh mục
function getProductsByCategory($conn, $categoryID)
{
    $result = array();
    if (!isset($categoryID) || !is_numeric($categoryID) || $categoryID <= 0) {
        $result = array('error' => 'ID danh mục không hợp lệ.');
        return $result;
    }
    $query = "SELECT * FROM products WHERE CategoryID = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        $result = array('error' => 'Không thể chuẩn bị câu truy vấn: ' . $conn->error);
        return $result;
    }
    $stmt->bind_param("i", $categoryID);
    if ($stmt->execute()) {
        $result_set = $stmt->get_result();
        while ($row = $result_set->fetch_assoc()) {
            $result[] = $row;
        }
    } else {
        $result = array('error' => 'Không thể lấy dữ liệu từ bảng products: ' . $stmt->error);
    }
    $stmt->close();
    return $result;
}

//Hàm lấy danh sách sản phẩm từ brand name
function getProductsByBrandname($conn, $brandID)
{
    // Khởi tạo biến kết quả
    $result = array();

    // Câu truy vấn SQL để lấy tất cả dữ liệu từ bảng products
    $query = "SELECT * FROM products WHERE brandID = $brandID";

    // Thực thi truy vấn
    $stmt = $conn->query($query);

    // Kiểm tra và lấy dữ liệu
    if ($stmt) {
        while ($row = $stmt->fetch_assoc()) {
            $result[] = $row; // Thêm từng dòng dữ liệu vào mảng kết quả
        }
    } else {
        // Xử lý lỗi nếu truy vấn thất bại
        $result = array('error' => 'Không thể lấy dữ liệu từ bảng products: ' . $conn->error);
    }

    // Trả về kết quả
    return $result;
}

//Hàm lấy sản phẩm nổi bật trang chủ
function getOutstandingProducts($conn)
{
    // Khởi tạo biến kết quả
    $result = array();

    // Câu truy vấn SQL để lấy tất cả dữ liệu từ bảng products
    $query = "SELECT * FROM products WHERE outstanding_products = 'true'";

    // Thực thi truy vấn
    $stmt = $conn->query($query);

    // Kiểm tra và lấy dữ liệu
    if ($stmt) {
        while ($row = $stmt->fetch_assoc()) {
            $result[] = $row; // Thêm từng dòng dữ liệu vào mảng kết quả
        }
    } else {
        // Xử lý lỗi nếu truy vấn thất bại
        $result = array('error' => 'Không thể lấy dữ liệu từ bảng products: ' . $conn->error);
    }

    // Trả về kết quả
    return $result;
}

// Hàm lấy danh sách tin tức
function getDataNews($conn)
{
    // Khởi tạo biến kết quả
    $result = array();

    // Câu truy vấn SQL để lấy tất cả dữ liệu từ bảng products
    $query = "SELECT * FROM news";

    // Thực thi truy vấn
    $stmt = $conn->query($query);

    // Kiểm tra và lấy dữ liệu
    if ($stmt) {
        while ($row = $stmt->fetch_assoc()) {
            $result[] = $row; // Thêm từng dòng dữ liệu vào mảng kết quả
        }
    } else {
        // Xử lý lỗi nếu truy vấn thất bại
        $result = array('error' => 'Không thể lấy dữ liệu từ bảng products: ' . $conn->error);
    }

    // Trả về kết quả
    return $result;
}

// Hàm lấy danh sách tin tức từ bảng news
function getNewsList($conn)
{
    $news = array();
    
    // Câu truy vấn SQL để lấy tất cả tin tức, sắp xếp theo thời gian tạo mới nhất
    $sql = "SELECT * FROM news ORDER BY create_time DESC";
    
    // Chuẩn bị truy vấn
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        $news = array('error' => 'Không thể chuẩn bị truy vấn: ' . $conn->error);
        return $news;
    }
    
    // Thực thi truy vấn
    $stmt->execute();
    
    // Lấy kết quả
    $result = $stmt->get_result();
    
    // Lấy dữ liệu
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $news[] = $row;
        }
    } else {
        $news = array('error' => 'Không thể lấy dữ liệu từ bảng news: ' . $stmt->error);
    }
    
    // Đóng statement
    $stmt->close();
    
    // Trả về kết quả
    return $news;
}

//Hàm lấy ra chi tiết tin tức
function getNewsDetails($conn, $news_id)
{
    // Khởi tạo mảng kết quả
    $product = array();

    // Đảm bảo news_id là số nguyên
    $news_id = (int)$news_id;

    // Câu truy vấn SQL để lấy chi tiết sản phẩm
    $query = "SELECT * FROM news WHERE news_id = $news_id LIMIT 1";

    // Thực thi truy vấn
    $stmt = $conn->query($query);

    // Kiểm tra và lấy dữ liệu
    if ($stmt && $stmt->num_rows > 0) {
        $product = $stmt->fetch_assoc(); // Lấy dòng dữ liệu đầu tiên
    } else {
        // Trả về lỗi nếu không tìm thấy sản phẩm
        $product = array('error' => 'Không tìm thấy sản phẩm với ID: ' . $news_id);
    }

    // Trả về kết quả
    return $product;
}

// Hàm lấy danh sách lĩnh vực
function getDataFields($conn)
{
    // Khởi tạo biến kết quả
    $result = array();

    // Câu truy vấn SQL để lấy tất cả dữ liệu từ bảng products
    $query = "SELECT * FROM fields";

    // Thực thi truy vấn
    $stmt = $conn->query($query);

    // Kiểm tra và lấy dữ liệu
    if ($stmt) {
        while ($row = $stmt->fetch_assoc()) {
            $result[] = $row; // Thêm từng dòng dữ liệu vào mảng kết quả
        }
    } else {
        // Xử lý lỗi nếu truy vấn thất bại
        $result = array('error' => 'Không thể lấy dữ liệu từ bảng products: ' . $conn->error);
    }

    // Trả về kết quả
    return $result;
}

// Hàm lấy danh sách dịch vụ
function getDataServices($conn)
{
    // Khởi tạo biến kết quả
    $result = array();

    // Câu truy vấn SQL để lấy tất cả dữ liệu từ bảng products
    $query = "SELECT * FROM services";

    // Thực thi truy vấn
    $stmt = $conn->query($query);

    // Kiểm tra và lấy dữ liệu
    if ($stmt) {
        while ($row = $stmt->fetch_assoc()) {
            $result[] = $row; // Thêm từng dòng dữ liệu vào mảng kết quả
        }
    } else {
        // Xử lý lỗi nếu truy vấn thất bại
        $result = array('error' => 'Không thể lấy dữ liệu từ bảng products: ' . $conn->error);
    }

    // Trả về kết quả
    return $result;
}

//Hàm lấy ra chi tiết lĩnh vực
function getFieldDetails($conn, $fieldId)
{
    // Khởi tạo mảng kết quả
    $product = array();

    // Đảm bảo fieldId là số nguyên
    $fieldId = (int)$fieldId;

    // Câu truy vấn SQL để lấy chi tiết sản phẩm
    $query = "SELECT * FROM fields WHERE fieldId = $fieldId LIMIT 1";

    // Thực thi truy vấn
    $stmt = $conn->query($query);

    // Kiểm tra và lấy dữ liệu
    if ($stmt && $stmt->num_rows > 0) {
        $product = $stmt->fetch_assoc(); // Lấy dòng dữ liệu đầu tiên
    } else {
        // Trả về lỗi nếu không tìm thấy sản phẩm
        $product = array('error' => 'Không tìm thấy sản phẩm với ID: ' . $fieldId);
    }

    // Trả về kết quả
    return $product;
}

//Hàm lấy danh sách sản phẩm theo lĩnh vực
function getProductsByFieldId($conn, $fieldId)
{
    // Khởi tạo biến kết quả
    $result = array();

    // Câu truy vấn SQL để lấy 4 sản phẩm theo fieldId
    $query = "SELECT * 
              FROM products 
              WHERE fieldId = ? 
              LIMIT 4";

    // Chuẩn bị truy vấn để tránh SQL Injection
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        return array('error' => 'Lỗi chuẩn bị truy vấn: ' . $conn->error);
    }

    // Gán tham số và thực thi truy vấn
    $stmt->bind_param("i", $fieldId);
    $stmt->execute();

    // Lấy kết quả
    $resultSet = $stmt->get_result();

    // Kiểm tra và lấy dữ liệu
    if ($resultSet) {
        while ($row = $resultSet->fetch_assoc()) {
            $result[] = $row; // Thêm từng dòng dữ liệu vào mảng kết quả
        }
    } else {
        // Xử lý lỗi nếu truy vấn thất bại
        $result = array('error' => 'Không thể lấy dữ liệu từ bảng products: ' . $conn->error);
    }

    // Đóng statement
    $stmt->close();

    // Trả về kết quả
    return $result;
}

//hàm insert dữ liệu liên hệ - trang contact
function insertContactCustomer($conn, $name, $email, $phone, $address, $subject, $content)
{
    // Câu truy vấn SQL để chèn dữ liệu
    $query = "INSERT INTO contact_customer (name, email, phone, address, subject, content, created_at) 
              VALUES (?, ?, ?, ?, ?, ?, NOW())";

    // Chuẩn bị truy vấn để tránh SQL Injection
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        return array('error' => 'Lỗi chuẩn bị truy vấn: ' . $conn->error);
    }

    // Gán tham số
    $stmt->bind_param("ssssss", $name, $email, $phone, $address, $subject, $content);

    // Thực thi truy vấn
    if ($stmt->execute()) {
        $result = array('success' => true, 'message' => 'Gửi yêu cầu liên hệ thành công!');
    } else {
        $result = array('error' => 'Lỗi khi chèn dữ liệu: ' . $stmt->error);
    }

    // Đóng statement
    $stmt->close();

    return $result;
}

//Hàm lấy ra chi tiết thương hiệu
function getBrandDetails($conn, $brandID)
{
    // Khởi tạo mảng kết quả
    $product = array();

    // Đảm bảo brandID là số nguyên
    $brandID = (int)$brandID;

    // Câu truy vấn SQL để lấy chi tiết sản phẩm
    $query = "SELECT * FROM brands WHERE brandID = $brandID LIMIT 1";

    // Thực thi truy vấn
    $stmt = $conn->query($query);

    // Kiểm tra và lấy dữ liệu
    if ($stmt && $stmt->num_rows > 0) {
        $product = $stmt->fetch_assoc(); // Lấy dòng dữ liệu đầu tiên
    } else {
        // Trả về lỗi nếu không tìm thấy sản phẩm
        $product = array('error' => 'Không tìm thấy sản phẩm với ID: ' . $brandID);
    }

    // Trả về kết quả
    return $product;
}

//Hàm lấy danh sách sản phẩm theo thương hiệu
function getProductsByBrand($conn, $brandID)
{
    // Khởi tạo biến kết quả
    $result = array();

    // Câu truy vấn SQL để lấy 4 sản phẩm theo brandID
    $query = "SELECT * 
              FROM products 
              WHERE brandID = ? 
              LIMIT 4";

    // Chuẩn bị truy vấn để tránh SQL Injection
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        return array('error' => 'Lỗi chuẩn bị truy vấn: ' . $conn->error);
    }

    // Gán tham số và thực thi truy vấn
    $stmt->bind_param("i", $brandID);
    $stmt->execute();

    // Lấy kết quả
    $resultSet = $stmt->get_result();

    // Kiểm tra và lấy dữ liệu
    if ($resultSet) {
        while ($row = $resultSet->fetch_assoc()) {
            $result[] = $row; // Thêm từng dòng dữ liệu vào mảng kết quả
        }
    } else {
        // Xử lý lỗi nếu truy vấn thất bại
        $result = array('error' => 'Không thể lấy dữ liệu từ bảng products: ' . $conn->error);
    }

    // Đóng statement
    $stmt->close();

    // Trả về kết quả
    return $result;
}

//Hàm lấy ra chi tiết dịch vụ
function getServiceDetails($conn, $serviceID)
{
    // Khởi tạo mảng kết quả
    $product = array();

    // Đảm bảo service_id  là số nguyên
    $service_id  = (int)$serviceID;

    // Câu truy vấn SQL để lấy chi tiết sản phẩm
    $query = "SELECT * FROM services WHERE service_id  = $service_id  LIMIT 1";

    // Thực thi truy vấn
    $stmt = $conn->query($query);

    // Kiểm tra và lấy dữ liệu
    if ($stmt && $stmt->num_rows > 0) {
        $product = $stmt->fetch_assoc(); // Lấy dòng dữ liệu đầu tiên
    } else {
        // Trả về lỗi nếu không tìm thấy sản phẩm
        $product = array('error' => 'Không tìm thấy sản phẩm với ID: ' . $service_id);
    }

    // Trả về kết quả
    return $product;
}

//Hàm lấy danh sách dịch vụ
function getAllServices($conn)
{
    // Khởi tạo mảng kết quả
    $services = array();

    // Câu truy vấn SQL để lấy tất cả thương hiệu
    $query = "SELECT * FROM services";

    // Thực thi truy vấn
    $stmt = $conn->query($query);

    // Kiểm tra và lấy dữ liệu
    if ($stmt) {
        while ($row = $stmt->fetch_assoc()) {
            $services[] = $row; // Thêm từng thương hiệu vào mảng
        }
    } else {
        // Xử lý lỗi nếu truy vấn thất bại
        $services = array('error' => 'Không thể lấy dữ liệu từ bảng services: ' . $conn->error);
    }

    // Trả về kết quả
    return $services;
}
function getCurrentCategory($conn, $category_id)
{
    // Khởi tạo mảng kết quả
    $category = array();

    // Đảm bảo category_id là số nguyên
    $category_id = (int)$category_id;

    // Câu truy vấn SQL để lấy chi tiết danh mục
    $query = "SELECT * FROM category WHERE CategoryID  = $category_id LIMIT 1";

    // Thực thi truy vấn
    $stmt = $conn->query($query);

    // Kiểm tra và lấy dữ liệu
    if ($stmt && $stmt->num_rows > 0) {
        $category = $stmt->fetch_assoc(); // Lấy dòng dữ liệu đầu tiên
    } else {
        // Trả về lỗi nếu không tìm thấy danh mục
        $category = array('error' => 'Không tìm thấy danh mục với ID: ' . $category_id);
    }

    // Trả về kết quả
    return $category;
}

// Hàm lấy danh sách tất cả danh mục với giới hạn
function getAllCategoriesLimit($conn)
{
    // Khởi tạo mảng kết quả
    $categories = array();

    // Giá trị LIMIT
    $limit = 8;

    // Câu truy vấn SQL với placeholder ?
    $query = "SELECT * FROM category LIMIT ?"; // Sửa tên bảng thành "categories" nếu cần

    // Chuẩn bị truy vấn
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        $categories = array('error' => 'Không thể chuẩn bị câu truy vấn: ' . $conn->error);
        return $categories;
    }

    // Bind giá trị LIMIT vào placeholder
    $stmt->bind_param("i", $limit); // "i" nghĩa là giá trị là kiểu integer

    // Thực thi truy vấn
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row; // Thêm từng danh mục vào mảng
        }
    } else {
        $categories = array('error' => 'Không thể lấy dữ liệu từ bảng categories: ' . $stmt->error);
    }

    // Đóng statement
    $stmt->close();

    // Trả về kết quả
    return $categories;
}

// Hàm lấy 4 lĩnh vực mới nhất
function getFieldsLimit4($conn, $limit = 4)
{
    $fields = [];

    // Truy vấn lấy danh sách lĩnh vực với LIMIT động
    $sql = "SELECT fieldId, fieldName, fieldDescription, fieldImg, createAt 
            FROM fields 
            LIMIT ?";

    $stmt = $conn->prepare($sql);

    // Gắn tham số limit vào câu truy vấn
    $stmt->bind_param("i", $limit); // "i" nghĩa là tham số là kiểu integer

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $fields[] = [
                'fieldId' => $row['fieldId'],
                'fieldName' => $row['fieldName'],
                'fieldDescription' => $row['fieldDescription'],
                'fieldImg' => $row['fieldImg'],
                'createAt' => $row['createAt']
            ];
        }
    }

    $stmt->close();

    return $fields;
}

// Hàm lấy 3 dịch vụ mới nhất
function getNewsLimit3($conn, $limit)
{
    // Khởi tạo mảng kết quả
    $news = array();

    // Kiểm tra giá trị $limit
    if (!is_numeric($limit) || $limit <= 0) {
        $news = array('error' => 'Giới hạn số lượng tin tức không hợp lệ.');
        return $news;
    }

    // Câu truy vấn SQL với placeholder ?
    $query = "SELECT * FROM news LIMIT ?"; // Sắp xếp theo ngày tạo mới nhất

    // Chuẩn bị truy vấn
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        $news = array('error' => 'Không thể chuẩn bị câu truy vấn: ' . $conn->error);
        return $news;
    }

    // Bind giá trị LIMIT vào placeholder
    $stmt->bind_param("i", $limit); // "i" nghĩa là giá trị là kiểu integer

    // Thực thi truy vấn
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $news[] = $row; // Thêm từng tin tức vào mảng
        }
    } else {
        $news = array('error' => 'Không thể lấy dữ liệu từ bảng news: ' . $stmt->error);
    }

    // Đóng statement
    $stmt->close();

    // Trả về kết quả
    return $news;
}

// Hàm xử lý thêm style font-family vào các thẻ mở trong HTML
function addFontFamilyToTags($htmlContent)
{
    // Biểu thức chính quy để tìm các thẻ mở (không bao gồm thẻ tự đóng như <img />)
    // - ([a-zA-Z]+): Tìm tên thẻ (h1, p, strong, v.v.)
    // - (?:\s+[^>]*)?: Tìm các thuộc tính hiện có (nếu có), không bắt nhóm này
    // - >: Kết thúc thẻ mở
    $pattern = '/<([a-zA-Z]+)(?:\s+[^>]*)?>/i';

    // Hàm thay thế để thêm style font-family
    $callback = function ($matches) {
        $tagName = $matches[1]; // Tên thẻ (h1, p, strong, v.v.)
        $fullTag = $matches[0]; // Toàn bộ thẻ mở

        // Nếu thẻ đã có thuộc tính style, thêm font-family vào style
        if (preg_match('/style="[^"]*"/i', $fullTag)) {
            $newTag = preg_replace('/style="([^"]*)"/i', 'style="$1 font-family: raleway;"', $fullTag);
        } else {
            // Nếu thẻ không có thuộc tính style, thêm mới
            $newTag = str_replace(">", ' style="font-family: raleway;">', $fullTag);
        }

        return $newTag;
    };

    // Thay thế tất cả thẻ mở trong nội dung HTML
    $modifiedContent = preg_replace_callback($pattern, $callback, $htmlContent);
    return $modifiedContent;
}

//Hàm lấy danh sách dự án

    function getProjectList($conn){
    $sql = "SELECT * FROM projects";
    $result = $conn->query($sql);
    $projects = array();
    while($row = $result->fetch_assoc()){
        $projects[] = $row;
    }
    return $projects;
}

//Hàm lấy chi tiết bài viết bởi id
function getProjectDetail($conn, $id)
{
    $sql = "SELECT * FROM projects WHERE project_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $post = $result->fetch_assoc();
    return $post;
}

// hàm lấy 2 sự kiện mới nhất (loại trừ bài viết hiện tại)
function getLatestProjects($conn, $limit = 2, $excludeId = null)
{
    // Khởi tạo mảng kết quả
    $projects = array();

    // Kiểm tra giá trị $limit
    if (!is_numeric($limit) || $limit <= 0) {
        $projects = array('error' => 'Giới hạn số lượng dự án không hợp lệ.');
        return $projects;
    }

    // Câu truy vấn SQL - thêm điều kiện loại trừ nếu có excludeId
    if ($excludeId && is_numeric($excludeId)) {
        $sql = "SELECT * FROM projects WHERE project_id != ? ORDER BY create_at DESC LIMIT ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            $projects = array('error' => 'Không thể chuẩn bị câu truy vấn: ' . $conn->error);
            return $projects;
        }
        $stmt->bind_param("ii", $excludeId, $limit);
    } else {
        $sql = "SELECT * FROM projects ORDER BY create_at DESC LIMIT ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            $projects = array('error' => 'Không thể chuẩn bị câu truy vấn: ' . $conn->error);
            return $projects;
        }
        $stmt->bind_param("i", $limit);
    }

    // Thực thi truy vấn
    $stmt->execute();
    
    // Lấy kết quả
    $result = $stmt->get_result();
    
    // Lấy dữ liệu
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $projects[] = $row;
        }
    } else {
        $projects = array('error' => 'Không thể lấy dữ liệu từ bảng projects: ' . $stmt->error);
    }

    // Đóng statement
    $stmt->close();

    // Trả về kết quả
    return $projects;
}

// Hàm lấy sự kiện trước và sau 
function getProjectNextPosts($conn, $currentId)
{
    $result = array('previous' => null, 'next' => null);
    
    // Lấy bài viết trước
    $sql = "SELECT * FROM projects WHERE project_id < ? ORDER BY project_id DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $currentId);
    $stmt->execute();
    $prev_result = $stmt->get_result();
    if ($prev_result->num_rows > 0) {
        $result['previous'] = $prev_result->fetch_assoc();
    }
    $stmt->close();
    
    // Lấy bài viết sau
    $sql = "SELECT * FROM projects WHERE project_id > ? ORDER BY project_id ASC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $currentId);
    $stmt->execute();
    $next_result = $stmt->get_result();
    if ($next_result->num_rows > 0) {
        $result['next'] = $next_result->fetch_assoc();
    }
    $stmt->close();
    
    return $result;
}

// hàm lấy 2 tin tức mới nhất (loại trừ bài viết hiện tại)
function getLatestNews($conn, $limit = 2, $excludeId = null)
{
    // Khởi tạo mảng kết quả
    $news = array();

    // Kiểm tra giá trị $limit
    if (!is_numeric($limit) || $limit <= 0) {
        $news = array('error' => 'Giới hạn số lượng tin tức không hợp lệ.');
        return $news;
    }

    // Câu truy vấn SQL - thêm điều kiện loại trừ nếu có excludeId
    if ($excludeId && is_numeric($excludeId)) {
        $sql = "SELECT * FROM news WHERE news_id != ? ORDER BY create_time DESC LIMIT ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            $news = array('error' => 'Không thể chuẩn bị câu truy vấn: ' . $conn->error);
            return $news;
        }
        $stmt->bind_param("ii", $excludeId, $limit);
    } else {
        $sql = "SELECT * FROM news ORDER BY create_time DESC LIMIT ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            $news = array('error' => 'Không thể chuẩn bị câu truy vấn: ' . $conn->error);
            return $news;
        }
        $stmt->bind_param("i", $limit);
    }

    // Thực thi truy vấn
    $stmt->execute();
    
    // Lấy kết quả
    $result = $stmt->get_result();
    
    // Lấy dữ liệu
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $news[] = $row;
        }
    } else {
        $news = array('error' => 'Không thể lấy dữ liệu từ bảng news: ' . $stmt->error);
    }

    // Đóng statement
    $stmt->close();

    // Trả về kết quả
    return $news;
}

// Hàm lấy tin tức trước và sau 
function getNewsNextPosts($conn, $currentId)
{
    $result = array('previous' => null, 'next' => null);
    
    // Lấy bài viết trước
    $sql = "SELECT * FROM news WHERE news_id < ? ORDER BY news_id DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $currentId);
    $stmt->execute();
    $prev_result = $stmt->get_result();
    if ($prev_result->num_rows > 0) {
        $result['previous'] = $prev_result->fetch_assoc();
    }
    $stmt->close();
    
    // Lấy bài viết sau
    $sql = "SELECT * FROM news WHERE news_id > ? ORDER BY news_id ASC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $currentId);
    $stmt->execute();
    $next_result = $stmt->get_result();
    if ($next_result->num_rows > 0) {
        $result['next'] = $next_result->fetch_assoc();
    }
    $stmt->close();
    
    return $result;
}