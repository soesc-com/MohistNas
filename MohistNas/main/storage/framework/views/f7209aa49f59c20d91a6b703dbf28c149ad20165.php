            <!-- ================================================================ -->

            <!-- 重启确认 -->
            <div class="modal fade" id="staticBackdropreboot" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true"><div class="modal-dialog"><div class="modal-content">
                <div class="modal-header"><h5 class="modal-title" id="staticBackdropLabel"><i class="bi bi-arrow-counterclockwise"></i>&nbsp;<?php echo e(__('main.Reboot')); ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                    <div class="modal-body">
                    &nbsp;<?php echo e(__('main.txt-Reboot')); ?>

                    </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal"><?php echo e(__('main.txt-Cancel')); ?></button>
                    <button id="staticBackdropreboot_Btn" type="button" class="btn btn-secondary"><?php echo e(__('main.txt-OK')); ?></button>
                </div>
            </div></div></div>

            <!-- 关机确认 -->
            <div class="modal fade" id="staticBackdropshutdown" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true"><div class="modal-dialog"><div class="modal-content">
                <div class="modal-header"><h5 class="modal-title" id="staticBackdropLabel"><i class="bi bi-arrow-counterclockwise"></i>&nbsp;<?php echo e(__('main.Shutdown')); ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                    <div class="modal-body">
                    &nbsp;<?php echo e(__('main.txt-Shutdown')); ?>

                    </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal"><?php echo e(__('main.txt-Cancel')); ?></button>
                    <button id="staticBackdropshutdown_Btn" type="button" class="btn btn-secondary"><?php echo e(__('main.txt-OK')); ?></button>
                </div>
            </div></div></div>

            <!-- 重启和关机确认执行 -->
            <script type="text/javascript">
                $('#staticBackdropreboot_Btn').click(function () {
                    window.location.href="/reboot";
                });
                $('#staticBackdropshutdown_Btn').click(function () {
                    window.location.href="/shutdown";
                });
            </script>
            
            <!-- 页首功能HTML -->
            <div class="row align-items-start" style="border-bottom:1px solid #666666;box-shadow: 0px 2px 0px 0px #ffffff;">

                <div class="col-1 align-self-start" style="width:220px;padding-left:8px;">
                    <!-- Logo区域 --><a href="/"><img src="/images/logo.200x46.png" style="border:0px;width:200px;height:46px;padding:0px;"></a>
                </div>

                <div class="col align-self-end" style="height:46px;">
                    <!-- 页首功能区域开始 -->
                    <ul class="nav justify-content-end">

                        <!-- 用户菜单 -->
                        <li class="nav-item dropdown" style="font-size:16px;">
                            <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false" style="padding-left: 0px;padding-right: 12px;"><i class="bi bi-person" style="line-height:28px;font-size:20px;"></i>&nbsp;<?php echo e(ucfirst($xUser)); ?></a>
                            <ul class="dropdown-menu">

                                <li><a class="dropdown-item" href="#" style="padding-top:0px;padding-bottom:0px;"><i class="bi bi-key"></i>&nbsp;<?php echo e(__('main.ChangePassword')); ?></a></li>
                                <li><a class="dropdown-item" href="#">Demo1</a></li>
                                <li><a class="dropdown-item" href="#">Demo2</a></li>
                                <li><a class="dropdown-item" href="#">Demo3</a></li>

                            </ul>
                        </li>

                        <!-- 首选项菜单 -->
                       <li class="nav-item dropdown" style="font-size:16px;">
                            <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false" style="padding-left: 0px;padding-right: 12px;"><i class="bi bi-gear" style="line-height:28px;font-size:20px;" title="<?php echo e(__('main.Settings')); ?>" data-bs-toggle="tooltip" data-bs-placement="bottom"></i></a>
                            <ul class="dropdown-menu">

                                <li><a class="dropdown-item" href="/index"><i class="bi bi-circle"></i>&nbsp;<?php echo e(__('main.page_home')); ?></a></li>
                                <li><a class="dropdown-item" href="/preferences"><i class="bi bi-gear"></i>&nbsp;<?php echo e(__('main.Preferences')); ?></a></li>
                                <li><a class="dropdown-item" href="/log"><i class="bi bi-journal-text"></i>&nbsp;<?php echo e(__('main.Log')); ?></a></li>
                                <li><a class="dropdown-item" href="/about"><i class="bi bi-exclamation-circle"></i>&nbsp;<?php echo e(__('main.About')); ?></a></li>

                            </ul>
                        </li>

                        <!-- 关机菜单 -->
                       <li class="nav-item dropdown" style="font-size:16px;">
                            <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false" style="padding-left: 0px;padding-right: 12px;"><i class="bi bi-power" style="line-height:28px;font-size:20px;" title="<?php echo e(__('main.Power')); ?>" data-bs-toggle="tooltip" data-bs-placement="bottom"></i></a>
                            <ul class="dropdown-menu">

                                <li><a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#staticBackdropreboot" href="/reboot" style="padding-top:0px;padding-bottom:0px;"><i class="bi bi-arrow-counterclockwise"></i>&nbsp;<?php echo e(__('main.Reboot')); ?></a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#staticBackdropshutdown" href="/shutdown" style="padding-top:0px;padding-bottom:0px;"><i class="bi bi-power"></i>&nbsp;<?php echo e(__('main.Shutdown')); ?></a></li>

                            </ul>
                        </li>

                        <!-- 登出菜单 -->
                        <li class="nav-item"><a href="/logout"><i class="bi bi-box-arrow-right" style="font-size:20px;line-height:46px;float:right;padding-right:8px;padding-left:2px;"  title="<?php echo e(__('main.Logout')); ?>" data-bs-toggle="tooltip" data-bs-placement="left"></i></a></li>
                    
                    </ul>
                    <!-- 页首功能区域结束 -->
                </div>

            </div>

<?php /**PATH /MohistNas/main/resources/views/part-header.blade.php ENDPATH**/ ?>