<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;

$companies = \backend\models\Company::find()->all();
?>

<!-- Include Tailwind CSS -->
<script src="https://cdn.tailwindcss.com"></script>
<!-- Include Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
<!-- FontAwesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    body, html {
        font-family: 'Kanit', sans-serif;
        height: 100%;
        margin: 0;
        background: transparent !important; 
    }
    .help-block {
        color: #ef4444; /* text-red-500 */
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }
    /* Hide the default layout wrapper if it has background */
    .wrapper, .content-wrapper, body {
        background-color: transparent !important;
    }
    
    .main-login-bg {
        background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #3b82f6 100%);
        min-height: 100vh;
        width: 100vw;
        position: fixed;
        top: 0;
        left: 0;
        z-index: -1;
    }
    
    /* Animation */
    @keyframes fade-in-up {
        0% { opacity: 0; transform: translateY(30px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-up {
        animation: fade-in-up 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    
    /* Glass effect */
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
</style>

<div class="main-login-bg">
    <!-- Decorative background elements -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden opacity-20 pointer-events-none">
        <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] rounded-full bg-blue-400 blur-[100px]"></div>
        <div class="absolute top-[60%] -right-[10%] w-[50%] h-[50%] rounded-full bg-indigo-500 blur-[120px]"></div>
    </div>
</div>

<div class="min-h-screen flex items-center justify-center p-4 relative z-10">
    <div class="max-w-md w-full glass-card rounded-[2rem] shadow-2xl overflow-hidden animate-fade-in-up">
        <div class="p-8 sm:p-10">
            <div class="text-center mb-8">
                <div class="inline-block p-3 rounded-full bg-blue-50 mb-4 shadow-sm">
                    <img src="../../backend/web/uploads/logo/mco_logo.png" alt="MCO Logo" class="h-14 w-auto object-contain">
                </div>
                <h2 class="text-3xl font-bold text-gray-800 tracking-tight">ยินดีต้อนรับ</h2>
                <p class="text-gray-500 text-sm mt-2">กรุณาลงชื่อเข้าใช้เพื่อเข้าสู่ระบบ</p>
            </div>

            <?php $form = ActiveForm::begin([
                'id' => 'login-form',
                'options' => ['class' => 'space-y-5'],
                'fieldConfig' => [
                    'template' => "{label}\n{input}\n{hint}\n{error}",
                    'labelOptions' => ['class' => 'block text-sm font-medium text-gray-700 mb-1.5'],
                ],
            ]); ?>

            <div>
                <?= $form->field($model, 'username')->textInput([
                    'autofocus' => true, 
                    'class' => 'w-full px-4 py-3.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all text-gray-800 bg-gray-50 hover:bg-white', 
                    'placeholder' => 'กรอกชื่อผู้ใช้งาน'
                ])->label('ชื่อผู้ใช้งาน (Username)') ?>
            </div>

            <div class="relative">
                <?= $form->field($model, 'password', [
                    'template' => '{label}<div class="relative">{input}<button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-blue-500 focus:outline-none transition-colors"><i class="fas fa-eye"></i></button></div>{error}'
                ])->passwordInput([
                    'class' => 'w-full px-4 py-3.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all text-gray-800 bg-gray-50 hover:bg-white pr-12', 
                    'placeholder' => 'กรอกรหัสผ่าน', 
                    'id' => 'password-field'
                ])->label('รหัสผ่าน (Password)') ?>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">บริษัท (Company)</label>
                <div class="relative">
                    <select name="login_company" class="w-full px-4 py-3.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all text-gray-800 bg-gray-50 hover:bg-white appearance-none cursor-pointer" required>
                        <option value="">-- เลือกบริษัท --</option>
                        <option value="100">ทั้งหมด (เฉพาะสิทธิ์ผู้ดูแลระบบ)</option>
                        <?php foreach ($companies as $company): ?>
                            <option value="<?= $company->id ?>"><?= Html::encode($company->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-400">
                        <i class="fas fa-chevron-down text-sm"></i>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between pt-1">
                <div class="flex items-center group">
                    <div class="relative flex items-start">
                        <div class="flex items-center h-5">
                            <input id="remember" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded cursor-pointer transition-colors">
                        </div>
                        <div class="ml-2 text-sm">
                            <label for="remember" class="font-medium text-gray-600 cursor-pointer group-hover:text-gray-900 transition-colors">จำฉันไว้ในระบบ</label>
                        </div>
                    </div>
                </div>
                <div class="text-sm">
                    <a href="#" class="font-medium text-blue-600 hover:text-blue-500 transition-colors">ลืมรหัสผ่าน?</a>
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full flex justify-center items-center py-3.5 px-4 border border-transparent rounded-xl shadow-lg text-base font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all transform hover:-translate-y-0.5">
                    <i class="fas fa-sign-in-alt mr-2"></i> เข้าสู่ระบบ
                </button>
            </div>

            <?php ActiveForm::end() ?>
        </div>
        
        <div class="bg-gray-50/50 px-8 py-5 border-t border-gray-100 text-center">
            <p class="text-xs text-gray-400 font-medium tracking-wide uppercase">
                &copy; <?= date('Y') ?> MCO GROUP. All rights reserved.
            </p>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var msg_error = '<?= \Yii::$app->session->getFlash('msg-error') ?>';
        if (msg_error != '') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'เข้าสู่ระบบไม่สำเร็จ',
                    text: msg_error,
                    confirmButtonText: 'ตกลง',
                    customClass: {
                        confirmButton: 'bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors'
                    },
                    buttonsStyling: false
                });
            } else {
                alert(msg_error);
            }
        }
        
        var togglePassword = document.getElementById('togglePassword');
        if (togglePassword) {
            togglePassword.addEventListener('click', function() {
                var icon = this.querySelector('i');
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
                
                var input = document.getElementById('password-field');
                if (input.getAttribute('type') === 'password') {
                    input.setAttribute('type', 'text');
                } else {
                    input.setAttribute('type', 'password');
                }
            });
        }
    });
</script>

