<?php
@session_start();
define('TTH_SYSTEM', true);

$_SESSION["language"] = 'vi';
require_once(str_replace(DIRECTORY_SEPARATOR, '/', dirname(__file__)) . '/define.php');
require_once(ROOT_DIR . DS . "lang" . DS . TTH_LANGUAGE . ".lang");
include_once(_F_FUNCTIONS . DS . "Function.php");

try {
    $db = new ActiveRecord(TTH_DB_HOST, TTH_DB_USER, TTH_DB_PASS, TTH_DB_NAME);
    include_once(_F_INCLUDES . DS . "_tth_constants.php");
} catch(Exception $e) {
    // Fallback nếu không kết nối được DB
}

$siteName = function_exists('getConstant') ? getConstant('meta_site_name') : 'DXMT';
?>
<!DOCTYPE html>
<html lang="<?php echo TTH_LANGUAGE; ?>">
<head>
    <?php
    include(_F_INCLUDES . DS . "_tth_head.php");
    include(_F_INCLUDES . DS . "_tth_script.php");
    ?>
    <title>404 - Trang không tồn tại | <?php echo $siteName; ?></title>
    <meta name="robots" content="noindex, nofollow">

    <style>
        /* 404 Page Specific Styles */
        .error-page-section {
            min-height: calc(100vh - 200px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 120px 20px 60px 20px;
            background: linear-gradient(135deg, #F9F6F3 0%, #F5EFE8 50%, #EDE4DB 100%);
            position: relative;
            overflow: hidden;
        }

        /* Background decoration */
        .error-page-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 80%;
            height: 150%;
            background: radial-gradient(ellipse, rgba(200, 154, 106, 0.08) 0%, transparent 70%);
            pointer-events: none;
        }

        .error-page-section::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 60%;
            height: 100%;
            background: radial-gradient(ellipse, rgba(139, 90, 60, 0.05) 0%, transparent 70%);
            pointer-events: none;
        }

        .error-page-content {
            text-align: center;
            max-width: 600px;
            width: 100%;
            position: relative;
            z-index: 10;
        }

        /* 404 Number */
        .error-page-number {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(8rem, 25vw, 14rem);
            font-weight: 700;
            line-height: 1;
            background: linear-gradient(135deg, #8B5A3C 0%, #C89A6A 50%, #8B5A3C 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
            position: relative;
            animation: error-float 3s ease-in-out infinite;
        }

        @keyframes error-float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .error-page-number::after {
            content: '404';
            position: absolute;
            top: 5px;
            left: 50%;
            transform: translateX(-50%);
            font-size: inherit;
            font-weight: inherit;
            background: linear-gradient(135deg, rgba(139, 90, 60, 0.1) 0%, rgba(200, 154, 106, 0.1) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            z-index: -1;
        }

        /* Decorative line */
        .error-page-line {
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, transparent, #C89A6A, transparent);
            margin: 1.5rem auto;
            border-radius: 2px;
        }

        /* Title */
        .error-page-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(1.5rem, 4vw, 2.25rem);
            font-weight: 600;
            color: #4A2E1F;
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 3px;
        }

        /* Description */
        .error-page-desc {
            font-size: 1rem;
            color: #6B5B51;
            line-height: 1.8;
            margin-bottom: 2.5rem;
        }

        /* Buttons */
        .error-page-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: center;
        }

        .error-page-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem 2rem;
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            border-radius: 50px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            border: none;
        }

        .error-page-btn-primary {
            background: linear-gradient(135deg, #8B5A3C 0%, #6B4530 100%);
            color: white !important;
            box-shadow: 0 4px 15px rgba(139, 90, 60, 0.3);
        }

        .error-page-btn-primary:hover {
            background: linear-gradient(135deg, #6B4530 0%, #4A2E1F 100%);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(139, 90, 60, 0.4);
            color: white !important;
            text-decoration: none;
        }

        .error-page-btn-secondary {
            background: transparent;
            color: #8B5A3C !important;
            border: 2px solid #C89A6A;
        }

        .error-page-btn-secondary:hover {
            background: #C89A6A;
            color: white !important;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(200, 154, 106, 0.3);
            text-decoration: none;
        }

        .error-page-btn i {
            font-size: 1.1rem;
            transition: transform 0.3s ease;
        }

        .error-page-btn-primary:hover i {
            transform: translateX(-3px);
        }

        .error-page-btn-secondary:hover i {
            transform: rotate(-45deg);
        }

        /* Decorative elements */
        .error-deco-circle {
            position: absolute;
            border-radius: 50%;
            border: 1px solid rgba(200, 154, 106, 0.2);
            pointer-events: none;
        }

        .error-deco-circle-1 {
            width: 300px;
            height: 300px;
            top: 10%;
            right: 5%;
            animation: error-pulse 4s ease-in-out infinite;
        }

        .error-deco-circle-2 {
            width: 200px;
            height: 200px;
            bottom: 15%;
            left: 8%;
            animation: error-pulse 4s ease-in-out infinite 1s;
        }

        .error-deco-circle-3 {
            width: 150px;
            height: 150px;
            top: 30%;
            left: 15%;
            animation: error-pulse 4s ease-in-out infinite 2s;
        }

        @keyframes error-pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.05); opacity: 0.8; }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .error-page-section {
                padding: 100px 15px 40px 15px;
                min-height: calc(100vh - 150px);
            }

            .error-page-number {
                margin-bottom: 0.5rem;
            }

            .error-page-title {
                letter-spacing: 2px;
            }

            .error-page-desc {
                font-size: 0.95rem;
                margin-bottom: 2rem;
            }

            .error-page-actions {
                flex-direction: column;
                align-items: center;
            }

            .error-page-btn {
                width: 100%;
                max-width: 280px;
                justify-content: center;
            }

            .error-deco-circle {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .error-page-section {
                padding: 90px 15px 30px 15px;
            }

            .error-page-title {
                font-size: 1.25rem;
                letter-spacing: 1px;
            }

            .error-page-desc {
                font-size: 0.9rem;
            }

            .error-page-btn {
                padding: 0.875rem 1.5rem;
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
<?php echo getConstant('script_body'); ?>

<!-- #wrapper -->
<div id="wrapper">
    <?php include(_F_INCLUDES . DS . "tth_header.php"); ?>

    <!-- 404 Error Section -->
    <section class="error-page-section">
        <!-- Decorative circles -->
        <div class="error-deco-circle error-deco-circle-1"></div>
        <div class="error-deco-circle error-deco-circle-2"></div>
        <div class="error-deco-circle error-deco-circle-3"></div>

        <div class="error-page-content">
            <div class="error-page-number">404</div>
            <div class="error-page-line"></div>
            <h1 class="error-page-title">Trang Không Tồn Tại222</h1>
            <p class="error-page-desc">
                Xin lỗi, trang bạn đang tìm kiếm không tồn tại hoặc đã được di chuyển.
                Vui lòng kiểm tra lại đường dẫn hoặc quay về trang chủ.
            </p>
            <div class="error-page-actions">
                <a href="<?php echo HOME_URL; ?>" class="error-page-btn error-page-btn-primary">
                    <i class="bi bi-arrow-left"></i>
                    Về Trang Chủ
                </a>
                <a href="<?php echo HOME_URL; ?>/lien-he" class="error-page-btn error-page-btn-secondary">
                    Liên Hệ
                    <i class="bi bi-arrow-up-right"></i>
                </a>
            </div>
        </div>
    </section>

    <?php include(_F_INCLUDES . DS . "tth_footer.php"); ?>
</div>
<!-- /#wrapper -->

</body>
</html>
