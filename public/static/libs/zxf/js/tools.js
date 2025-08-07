!function (global, document) {
    "use strict";

    /**
     * 表单验证工具类
     * @class FormHandle
     */
    var FormHandle = {
        /**
         * 初始化方法
         * @method init
         */
        init: function() {
            this.initConfig();
            this.initRules();
            this.initFileInputs();
            this.initFormEvents();
            this.initFormInterceptor();
            this.setupLayoutSwitcher();
        },

        /**
         * 初始化配置
         * @method initConfig
         */
        initConfig: function() {
            this.config = {
                formSelector: 'form',
                errorClass: 'error',
                errorMessageClass: 'error-message',
                successClass: 'success',
                loadingClass: 'loading',
                formLayouts: ['left', 'top', 'inline'],
                requiredFieldClass: 'required',
                filePreview: true,
                autoValidate: true,
                validateOnBlur: true,
                validateOnChange: false,
                scrollToError: true,
                scrollOffset: 20
            };
        },

        /**
         * 初始化验证规则
         * @method initRules
         */
        initRules: function() {
            this.rules = {
                // 必填验证
                required: {
                    validate: function(value, param, field) {
                        if (field.type === 'file') return field.files && field.files.length > 0;
                        return !Functions.isEmpty( value);
                    }.bind(this),
                    message: '此项为必填项'
                },

                // 邮箱验证
                email: {
                    validate: function(value) {
                        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
                    },
                    message: '请输入有效的邮箱地址'
                },

                // 手机号验证
                phone: {
                    validate: function(value) {
                        return /^1[3-9]\d{9}$/.test(value);
                    },
                    message: '请输入有效的手机号码'
                },

                // 数字验证
                number: {
                    validate: function(value) {
                        return !isNaN(value) && !isNaN(parseFloat(value));
                    },
                    message: '请输入有效的数字'
                },

                // 中文验证
                zh_CN: {
                    validate: function(value) {
                        return /^[\u4e00-\u9fa5]+$/.test(value);
                    },
                    message: '请输入中文'
                },

                // 强密码验证
                strong_password: {
                    validate: function(value) {
                        return /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[^]{8,20}$/.test(value);
                    },
                    message: '密码必须包含大小写字母和数字，长度8-20位'
                },

                // 相同值验证
                same: {
                    validate: function(value, param) {
                        const otherField = document.querySelector('[name="' + param + '"]');
                        return otherField && value === otherField.value;
                    },
                    message: function(param) {
                        return '必须与' + param + '保持一致';
                    }
                },

                // 文件类型验证
                file: {
                    validate: function(value, param, field) {
                        if (!field.files || field.files.length === 0) return false;

                        const file = field.files[0];
                        const allowedExtensions = param.split(',');
                        const fileExt = file.name.split('.').pop().toLowerCase();

                        // 检查文件扩展名
                        if (allowedExtensions.length > 0) {
                            return allowedExtensions.some(ext => {
                                return fileExt === ext.toLowerCase();
                            });
                        }

                        return true;
                    }.bind(this),
                    message: function(param) {
                        const exts = param.split(',');
                        if (exts.length > 0) {
                            return '请上传以下类型的文件: ' + exts.join(', ');
                        }
                        return '请上传有效的文件';
                    }
                },

                // 文件大小验证
                max_size: {
                    validate: function(value, param, field) {
                        if (!field.files || field.files.length === 0) return false;
                        const file = field.files[0];
                        return file.size <= param * 1024 * 1024;
                    },
                    message: function(param) {
                        return `文件大小不能超过${param}MB`;
                    }
                },

                // 最小长度验证
                min: {
                    validate: function(value, param) {
                        return value.length >= parseInt(param);
                    },
                    message: function(param) {
                        return `长度不能少于${param}个字符`;
                    }
                },

                // 最大长度验证
                max: {
                    validate: function(value, param) {
                        return value.length <= parseInt(param);
                    },
                    message: function(param) {
                        return `长度不能超过${param}个字符`;
                    }
                },

                // 长度范围验证
                len: {
                    validate: function(value, param) {
                        const [min, max] = param.split(',').map(Number);
                        return value.length >= min && value.length <= max;
                    },
                    message: function(param) {
                        const [min, max] = param.split(',');
                        return `长度必须在${min}到${max}个字符之间`;
                    }
                },

                // 数值范围验证
                between: {
                    validate: function(value, param) {
                        const [min, max] = param.split(',').map(Number);
                        const numValue = parseFloat(value);
                        return numValue >= min && numValue <= max;
                    },
                    message: function(param) {
                        const [min, max] = param.split(',');
                        return `值必须在${min}到${max}之间`;
                    }
                },

                // 枚举值验证
                in: {
                    validate: function(value, param) {
                        const options = param.split(',');
                        return options.includes(value);
                    },
                    message: function(param) {
                        const options = param.split(',');
                        return `只能输入以下值: ${options.join(', ')}`;
                    }
                },

                // 日期格式验证
                date: {
                    validate: function(value) {
                        return /^\d{4}-\d{2}-\d{2}$/.test(value) && !isNaN(new Date(value).getTime());
                    },
                    message: '请输入有效的日期格式(YYYY-MM-DD)'
                },

                // URL验证
                url: {
                    validate: function(value) {
                        return /^(https?|ftp):\/\/[^\s/$.?#].[^\s]*$/.test(value);
                    },
                    message: '请输入有效的URL地址'
                },

                // 身份证验证
                id_card: {
                    validate: function(value) {
                        // 简单验证，实际项目中应使用更严格的验证
                        return /^[1-9]\d{5}(18|19|20)\d{2}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])\d{3}[\dXx]$/.test(value);
                    },
                    message: '请输入有效的身份证号码'
                },

                // 邮政编码验证
                postal_code: {
                    validate: function(value) {
                        return /^[1-9]\d{5}$/.test(value);
                    },
                    message: '请输入有效的邮政编码'
                },

                // IP地址验证
                ip: {
                    validate: function(value) {
                        return /^(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)$/.test(value);
                    },
                    message: '请输入有效的IP地址'
                },

                // 信用卡验证
                credit_card: {
                    validate: function(value) {
                        // Luhn算法验证信用卡号
                        value = value.replace(/\s+/g, '');
                        if (!/^\d+$/.test(value)) return false;

                        let sum = 0;
                        let alternate = false;
                        for (let i = value.length - 1; i >= 0; i--) {
                            let digit = parseInt(value.charAt(i));
                            if (alternate) {
                                digit *= 2;
                                if (digit > 9) {
                                    digit = (digit % 10) + 1;
                                }
                            }
                            sum += digit;
                            alternate = !alternate;
                        }
                        return sum % 10 === 0;
                    },
                    message: '请输入有效的信用卡号'
                },

                remote: {
                    /**
                     * 远程验证
                     * @param value 验证的值
                     * @param url 远程验证的URL
                     * @param field 当前字段对象
                     * @returns {Promise<*|boolean>}
                     */
                    validate: async function(value, url, field) {
                        let _this = this;
                        if(Functions.isEmpty(value)){
                            return true;
                        }
                        try {
                            let join_callback = field.getAttribute('data-join');
                            let tempJson = {};
                            if(join_callback){
                                // 判断 join_callback 是否为 json 字符串 或者函数
                                if(typeof join_callback === 'string'){
                                    if(Functions.is_function(join_callback)){
                                        tempJson = window[join_callback](field);
                                    }else if(Functions.is_json(join_callback) ){
                                        join_callback = JSON.parse(join_callback);
                                        tempJson = join_callback;
                                    }
                                }else{
                                    console.error('验证规则执行错误:remote');
                                    return false;
                                }
                            }
                            tempJson[field.getAttribute('name')] = value;

                            const formData = new FormData(); // 不需要手动设置 Content-Type，浏览器会自动设置
                            // 把 tempJson 的数据全部添加到 formData 中
                            for (let key in tempJson) {
                                formData.append(key, tempJson[key]);
                            }
                            // 发送请求
                            const response = await fetch(url, {
                                method: 'POST',
                                body: formData
                            });

                            if (!response.ok){
                                // 尝试读取响应内容并打印
                                return response.text().then(text => {
                                    // 判断 text 是否为 JSON
                                    if(Functions.is_json(text)){
                                        let res = JSON.parse(text);
                                        _this.message = response.status+ ':' + (res.message || res.msg || '请求失败');
                                        return false;
                                    }else{
                                        _this.message = '远程验证请求失败: ' + response.status;
                                        return false;
                                    }
                                });
                            }
                            const result = await response.json();

                            if( result.check || result.code === 200){
                                return true;
                            }
                            _this.message = result.message || result.msg || '验证失败';
                            return false;
                        } catch (error) {
                            _this.message = '远程验证请求失败: ' + error.message;
                            return false;
                        }
                    },
                    message: '远程验证失败'
                },

                // 可空验证
                nullable: {
                    validate: function(value, param, field) {
                        if (field.type === 'file') {
                            return !field.files || field.files.length === 0 || true;
                        }
                        return Functions.isEmpty(value) || true;
                    }.bind(this),
                    message: ''
                }
            };
        },

        /**
         * 初始化文件输入框事件监听
         * @method initFileInputs
         */
        initFileInputs: function() {
            document.querySelectorAll('input[type="file"]').forEach(input => {
                const wrapper = input.closest('.file-input-wrapper');
                const fileInfo = wrapper?.querySelector('.file-info');
                const filePreview = wrapper?.querySelector('.file-preview');

                // 文件变更事件
                input.addEventListener('change', function(e) {
                    if (this.files && this.files.length > 0) {
                        const file = this.files[0];
                        if(fileInfo) {
                            fileInfo.querySelector('span').textContent = `已选择: ${file.name} (${(file.size / 1024 / 1024).toFixed(2)}MB)`;
                        }
                        // 文件预览
                        if (FormHandle.config.filePreview && file.type.startsWith('image/') && filePreview) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                filePreview.src = e.target.result;
                                filePreview.style.display = 'block';
                            };
                            reader.readAsDataURL(file);
                        }

                        // 清除错误状态
                        FormHandle.clearError(this);
                    } else {
                        if(fileInfo) {fileInfo.querySelector('span').textContent = '点击或拖拽文件到此处';}
                        if (filePreview) filePreview.style.display = 'none';
                    }
                });

                // 拖拽事件
                if (wrapper) {
                    wrapper.addEventListener('dragover', function(e) {
                        e.preventDefault();
                        this.style.borderColor = 'var(--primary-color)';
                        this.style.backgroundColor = 'rgba(66, 133, 244, 0.05)';
                    });

                    wrapper.addEventListener('dragleave', function(e) {
                        e.preventDefault();
                        this.style.borderColor = '';
                        this.style.backgroundColor = '';
                    });

                    wrapper.addEventListener('drop', function(e) {
                        e.preventDefault();
                        this.style.borderColor = '';
                        this.style.backgroundColor = '';
                        if (e.dataTransfer.files.length > 0) {
                            input.files = e.dataTransfer.files;
                            const event = new Event('change');
                            input.dispatchEvent(event);
                        }
                    });
                }
            });
        },

        /**
         * 初始化表单事件监听
         * @method initFormEvents
         */
        initFormEvents: function() {
            // 文本域字数统计
            document.querySelectorAll('textarea[maxlength], textarea[data-rule*="max"]').forEach(textarea => {
                const max = textarea.maxLength || parseInt(textarea.getAttribute('data-rule').match(/max:(\d+)/)?.[1]) || 0;
                if (max > 0) {
                    const counter = textarea.nextElementSibling;
                    if (counter && counter.classList.contains('form-notice')) {
                        textarea.addEventListener('input', function() {
                            counter.textContent = `${this.value.length}/${max}`;
                            if (this.value.length > max * 0.9) {
                                counter.style.color = 'var(--warning-color)';
                            } else {
                                counter.style.color = 'var(--secondary-text)';
                            }
                        });

                        // 初始化计数器
                        counter.textContent = `${textarea.value.length}/${max}`;
                    }
                }
            });

            // 输入框实时验证
            if (this.config.autoValidate) {
                document.querySelectorAll('[data-rule]').forEach(field => {
                    if (this.config.validateOnBlur) {
                        field.addEventListener('blur', function() {
                            FormHandle.validateField(this);
                        });
                    }

                    if (this.config.validateOnChange) {
                        field.addEventListener('input', function() {
                            FormHandle.validateField(this);
                        });
                    }

                    // 对于单选和复选框，添加change事件
                    if (field.type === 'radio' || field.type === 'checkbox') {
                        field.addEventListener('change', function() {
                            FormHandle.validateField(this);
                        });
                    }
                });
            }
        },

        /**
         * 设置表单布局切换器
         * @method setupLayoutSwitcher
         */
        setupLayoutSwitcher: function() {
            const switchers = document.querySelectorAll('.layout-switcher');
            switchers.forEach(switcher => {
                const buttons = switcher.querySelectorAll('.layout-btn');
                buttons.forEach(btn => {
                    btn.addEventListener('click', function() {
                        buttons.forEach(b => b.classList.remove('active'));
                        this.classList.add('active');
                    });
                });
            });
        },
        // 触发提交表单处理
        formSubmit: function(event,form=null) {
            FormHandle.handleFormSubmit(event,form);
        },
        /**
         * 初始化表单拦截器
         * @method initFormInterceptor
         */
        initFormInterceptor: function() {
            const forms = document.querySelectorAll(this.config.formSelector);

            forms.forEach(form => {
                // 拦截表单提交
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    FormHandle.handleFormSubmit(e);
                });

                // 拦截提交按钮点击
                const submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
                submitButtons.forEach(button => {
                    button.addEventListener('click', function(e) {
                        if (e.target.type === 'submit') {
                            e.preventDefault();
                            FormHandle.handleFormSubmit(e);
                        }
                    });
                });

                // 处理回车键提交
                form.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA' &&
                        e.target.type !== 'submit' && e.target.type !== 'button') {
                        e.preventDefault();
                        FormHandle.handleFormSubmit(e);
                    }
                });
            });
        },

        /**
         * 验证单个字段
         * @method validateField
         * @param {HTMLElement} field - 表单字段元素
         * @param form
         * @return {Promise<boolean>} 是否验证通过
         */
        validateField: async function(field,form =  null) {
            const rules = field.getAttribute('data-rule')?.split('|').filter(rule => rule.trim() !== '') || [];

            // 如果是nullable且值为空，跳过验证
            if (rules.includes('nullable')) {
                if (field.type === 'file') {
                    if (!field.files || field.files.length === 0) {
                        this.clearError(field);
                        return true;
                    }
                } else if (Functions.isEmpty(field.value)) {
                    this.clearError(field);
                    return true;
                }
            }

            let isValid = true;
            let firstErrorMessage = '';

            for (const rule of rules) {
                if (rule === 'nullable') continue;

                let [ruleName, param] = rule.split(':');
                let ruleDef = this.rules[ruleName];

                if (!ruleDef) {
                    // 判断 rule 规则 是否为 remote(xxx) 的形式，如果是:ruleName 的值为 remote；param的值为 xxx
                    if (ruleName.startsWith('remote(')) {
                        ruleName = 'remote';
                        param = rule.slice(7, -1); // url
                        ruleDef = this.rules[ruleName];
                    }else{
                        console.warn('未知的验证规则:', ruleName);
                        continue;
                    }
                }

                let validationResult;
                let fieldValue = field.value;

                // 特殊字段处理
                if (field.type === 'checkbox' || field.type === 'radio') {
                    // 处理单选按钮组
                    if (field.type === 'radio') {
                        fieldValue = Functions.getRadioValue(field.name, form);
                    }else{
                        fieldValue = Functions.getCheckboxValue(field.name, form);
                    }
                } else if (field.type === 'file') {
                    fieldValue = field;
                }

                try {
                    // 处理远程验证
                    if (ruleName === 'remote') {
                        validationResult = await ruleDef.validate(fieldValue, param, field);
                    } else {
                        validationResult = ruleDef.validate(fieldValue, param, field);
                    }

                    if (!validationResult) {
                        isValid = false;
                        const message = typeof ruleDef.message === 'function'
                            ? ruleDef.message(param)
                            : ruleDef.message;

                        if (!firstErrorMessage) firstErrorMessage = message;
                        break;
                    }
                } catch (error) {
                    console.error('验证规则执行错误:', ruleName, error);
                    isValid = false;
                    if (!firstErrorMessage) firstErrorMessage = '验证过程出错';
                    break;
                }
            }

            if (!isValid) {
                this.showError(field, firstErrorMessage);
            } else {
                this.clearError(field);
                this.showSuccess(field);
            }

            return isValid;
        },

        /**
         * 显示错误信息
         * @method showError
         * @param {HTMLElement} field - 表单字段元素
         * @param {string} message - 错误信息
         */
        showError: function(field, message) {
            // 添加错误类
            if (field.type === 'file') {
                const wrapper = field.closest('.file-input-wrapper');
                if (wrapper) wrapper.classList.add(this.config.errorClass);
            } else {
                field.classList.add(this.config.errorClass);
            }

            // 显示错误消息
            let errorMessage = this.findErrorMessageElement(field);
            if (errorMessage) {
                errorMessage.textContent = message;
                errorMessage.classList.add('show');
            }
        },

        /**
         * 显示成功状态
         * @method showSuccess
         * @param {HTMLElement} field - 表单字段元素
         */
        showSuccess: function(field) {
            field.classList.remove(this.config.errorClass);
            field.classList.add(this.config.successClass);

            // 延迟移除成功状态
            setTimeout(() => {
                field.classList.remove(this.config.successClass);
            }, 2000);
        },

        /**
         * 查找错误消息元素
         * @method findErrorMessageElement
         * @param {HTMLElement} field - 表单字段元素
         * @return {HTMLElement|null} 错误消息元素
         */
        findErrorMessageElement: function(field) {

            // 对于单选按钮，错误消息可能在父容器中
            const container = field.closest(".form-control-container");
            if (container) {
                return container.querySelector("." + this.config.errorMessageClass);
            }

            // 对于文件输入，错误消息可能在包装元素后面
            const wrapper = field.closest(".file-input-wrapper");
            if (wrapper && wrapper.nextElementSibling &&
                wrapper.nextElementSibling.classList.contains(this.config.errorMessageClass)) {
                return wrapper.nextElementSibling;
            }

            // 对于常规字段，错误消息可能是下一个兄弟元素
            if (field.nextElementSibling &&
                field.nextElementSibling.classList.contains(this.config.errorMessageClass)) {
                return field.nextElementSibling;
            }

            // bs-4

            // 判断 field 元素的祖先元素中是否有 .row 或者 .form-group
            const parent = field.closest(".form-group, .row");
            if (parent) {
                let findShowErrorEle = parent.querySelector("." + this.config.errorMessageClass);

                if (findShowErrorEle) {
                    return findShowErrorEle;
                } else {
                    if(field.classList.contains('custom-select')){
                        field.parentElement.insertAdjacentHTML("afterend", `<span class="${this.config.errorMessageClass}"></span>`);
                        return field.parentElement.nextElementSibling;
                    }else{
                        // 在 field 元素之后添加一个错误消息元素 span.error-message
                        field.insertAdjacentHTML("afterend", `<span class="${this.config.errorMessageClass}"></span>`);
                        return field.nextElementSibling;
                    }
                }
            }

            return null;
        },

        /**
         * 清除错误信息
         * @method clearError
         * @param {HTMLElement} field - 表单字段元素
         */
        clearError: function(field) {
            // 移除错误类
            if (field.type === 'file') {
                const wrapper = field.closest('.file-input-wrapper');
                if (wrapper) wrapper.classList.remove(this.config.errorClass);
            } else {
                field.classList.remove(this.config.errorClass);
            }

            // 清除错误消息
            let errorMessage = this.findErrorMessageElement(field);

            if (errorMessage) {
                errorMessage.textContent = '';
                errorMessage.classList.remove('show');
            }
        },

        /**
         * 处理表单提交
         * @method handleFormSubmit
         * @param {Event} event - 事件对象
         * @param formObj - 表单对象
         */
        handleFormSubmit: async function(event,formObj = null) {
            const form = formObj ||event.target.closest('form');
            if (!form) return;

            // 获取提交按钮
            let submitButton = null;
            if (event.type === 'click' && (event.target.type === 'submit' || event.target.tagName === 'BUTTON')) {
                submitButton = event.target;
            } else {
                submitButton = form.querySelector('button[type="submit"], input[type="submit"]');
            }

            // 保存原始按钮状态
            const originalButtonDisabled = submitButton ? submitButton.disabled : false;

            // 禁用按钮并显示加载状态
            if (submitButton) {
                submitButton.disabled = true;
                const submitText = submitButton.querySelector('#submitText') || submitButton;
                submitText.insertAdjacentHTML('afterend', '<span class="loading-spinner"></span>');
            }

            try {
                // 清除之前的错误提示
                this.clearFormErrors(form);

                // 验证表单
                const isValid = await this.validateForm(form);

                if (!isValid) {
                    // 滚动到第一个错误处
                    if (this.config.scrollToError) {
                        this.scrollToFirstError(form);
                    }
                    return;
                }

                const method = form.getAttribute('method') || 'POST';
                // 检查是否使用AJAX提交 || 或者使用 POST方法提交
                let useAjax = form.hasAttribute("use-ajax") || method.toUpperCase() === "POST";
                if(form.hasAttribute('not-use-ajax')){
                    // 使用 not-use-ajax 标识不使用AJAX 提交
                    useAjax = false;
                }

                //表单提交前的操作
                if (typeof (form_before) === "function") {
                    if (form_before(event) === false) {
                        return false;
                    }
                }

                if (useAjax) {
                    // 使用AJAX提交表单
                    await this.submitFormViaAjax(form, submitButton);
                } else {
                    // 使用传统方式提交表单
                    form.submit();
                }
            } catch (error) {
                console.error('表单提交错误:', error);
                this.showFormError(form, '表单提交过程中出错，请重试');
            } finally {
                // 恢复按钮状态
                if (submitButton) {
                    submitButton.disabled = originalButtonDisabled;
                    const spinner = submitButton.querySelector('.loading-spinner');
                    if (spinner) spinner.remove();
                }
            }
        },

        /**
         * 获取表单或容器内的结构化表单数据/获取某个div或者form 内的表单
         * @param {string|HTMLElement} target - 选择器或DOM元素
         * @param {Object} [options={}] - 配置选项
         * @returns {Object} 结构化的表单数据
         */
        getDivFormData: function(target, options = {}) {
            const {
                includeEmpty = false, // 是否包含空字段
                includeDisabled = false, // 是否包含禁用字段
                includeReadonly = false, // 是否包含只读字段
                ignoreTypes = ['hidden', 'button', 'submit', 'reset', 'image'], // 忽略的字段类型
                booleanFields = true // 布尔字段
            } = options;

            const form = typeof target === 'string'
                ? document.querySelector(target)
                : target;

            if (!form) return {};

            const fieldSelector = [
                'input:not([type="' + ignoreTypes.join('"]):not([type="') + '"])',
                'textarea',
                'select'
            ].join(',');

            return Array.from(form.elements || form.querySelectorAll(fieldSelector))
                .reduce((data, element) => {
                    if ((!includeDisabled && element.disabled) ||
                        (!includeReadonly && element.readOnly)) {
                        return data;
                    }

                    const { name, type, checked, value, multiple, selectedOptions } = element;
                    if (!name && !includeEmpty) return data;

                    let fieldValue;
                    switch (true) {
                        case type === 'checkbox':
                            fieldValue = booleanFields ? checked : (checked ? value : undefined);
                            break;
                        case type === 'radio':
                            if (!checked) return includeEmpty ? { ...data, [name]: undefined } : data;
                            fieldValue = value;
                            break;
                        case multiple:
                            fieldValue = Array.from(selectedOptions).map(opt => opt.value);
                            break;
                        default:
                            fieldValue = value;
                    }

                    if (includeEmpty || (fieldValue !== undefined && fieldValue !== '')) {
                        data[name] = fieldValue;
                    }

                    return data;
                }, {});
        },

        /**
         * 表单数据收集器/获取表单数据
         * @param {HTMLFormElement|string} form - 表单元素或选择器
         * @param {Object} [options] - 配置选项
         * @param {boolean} [options.includeDisabled=false] - 是否包含禁用字段
         * @param {boolean} [options.includeEmpty=true] - 是否包含空值字段
         * @param {boolean} [options.includeButtons=false] - 是否包含按钮
         * @param {Function} [options.filter] - 自定义字段过滤器
         * @returns {FormData|URLSearchParams|Object} - 返回FormData、URLSearchParams或普通对象
         */
        getFormData: function(form, options = {}) {
            if (!form) {
                console.error('未找到表单元素');
                return null;
            }

            // 参数标准化
            const formElement = typeof form === 'string'
                ? document.querySelector(form)
                : form;

            if (!formElement || !formElement.tagName || formElement.tagName !== 'FORM') {
                throw new Error('Invalid form element provided');
            }

            // 合并默认选项
            const {
                includeDisabled = false,
                includeEmpty = true,
                includeButtons = false,
                filter = null
            } = options;

            // 检测是否有文件
            const hasFile = Array.from(formElement.elements).some(el =>
                el.type === 'file' && el.files.length > 0
            );

            // 创建合适的数据容器
            const formDataObj = hasFile ? new FormData() : new URLSearchParams();
            const resultObject = {};

            // 遍历所有表单元素
            Array.from(formElement.elements).forEach(element => {
                const { tagName, type, name, disabled, value, checked, multiple, files } = element;

                // 跳过条件判断
                if (!name ||
                    (!includeDisabled && disabled) ||
                    (!includeButtons && (
                        tagName === 'BUTTON' ||
                        (tagName === 'INPUT' && (type === 'button' || type === 'submit' || type === 'reset'))
                    )) ||
                    (filter && !filter(element))
                ) {
                    return;
                }

                // 处理不同类型元素
                let values = [];

                if (tagName === 'SELECT') {
                    const options = element.selectedOptions;
                    values = multiple
                        ? Array.from(options).map(opt => opt.value)
                        : (element.selectedIndex >= 0 ? [options[0].value] : []);
                }
                else if (type === 'checkbox') {
                    if (checked) values = [value || 'on'];
                }
                else if (type === 'radio') {
                    if (checked) values = [value || 'on'];
                }
                else if (type === 'file') {
                    values = files.length > 0 ? Array.from(files) : [];
                }
                else {
                    values = [value];
                }

                // 过滤空值
                if (!includeEmpty && values.every(v => v === '' || v === null || v === undefined)) {
                    return;
                }

                // 添加到数据容器
                values.forEach(val => {
                    if (hasFile && type === 'file') {
                        formDataObj.append(name, val);
                    }
                    else if (hasFile) {
                        formDataObj.append(name, val);
                    }
                    else {
                        formDataObj.append(name, val);
                    }

                    // 构建结果对象
                    if (!resultObject[name]) {
                        resultObject[name] = values.length > 1 ? values : values[0];
                    }
                });
            });

            return hasFile ? formDataObj : resultObject;
        },

        /**
         * 使用AJAX提交表单
         * @method submitFormViaAjax
         * @param {HTMLFormElement} form - 表单元素
         * @param {HTMLElement} submitButton - 提交按钮
         * @param event - 提交按钮的点击事件对象
         * @return {Promise<void>} AJAX请求的Promise
         */
        submitFormViaAjax: function(form, submitButton) {
            const action = form.getAttribute('action') || window.location.href;
            const method = form.getAttribute('method') || 'POST';

            // 获取表单数据
            const formData = this.getFormData(form) || {};

            let config = {
                headers: {
                    // 'Content-Type': 'multipart/form-data',
                    'X-Requested-With': 'XMLHttpRequest', // 标识为 AJAX 请求
                    'X-CSRF-TOKEN': Functions.getCsrfToken() || '' // 添加CSRF令牌（如果存在）
                }
            };
            // 根据是否有文件决定Content-Type
            if (formData instanceof FormData) {
                // 对于FormData，浏览器会自动设置Content-Type和boundary
                // config.body = formData;
            } else {
                config.headers['Content-Type'] = 'application/x-www-form-urlencoded';
            }

            return myTools.http.request(method,action,formData,config).then(
                data => {
                    //表单提交后的操作
                    if (typeof (form_after) === "function") {
                        form_after(data);
                    }else{
                        console.log('可以定义一个form_after(resp)方法接管处理AJAX数据',data);
                    }
                }
            ).catch(
                error => {
                    //表单提交后的操作
                    if (typeof (form_after) === "function") {
                        form_after(error);
                    }else{
                        console.log('可以定义一个form_after(resp)方法接管处理AJAX数据',error);
                    }
                }
            );
        },

        /**
         * 验证整个表单
         * @method validateForm
         * @param {HTMLElement} form - 表单元素
         * @return {Promise<boolean>} 是否验证通过
         */
        validateForm: async function(form) {
            let isValid = true;
            const fields = form.querySelectorAll('[data-rule]');

            for (const field of fields) {
                const fieldValid = await this.validateField(field,form);
                if (!fieldValid) {
                    isValid = false;
                }
            }

            return isValid;
        },

        /**
         * 清除表单所有错误
         * @method clearFormErrors
         * @param {HTMLElement} form - 表单元素
         */
        clearFormErrors: function(form) {
            const errorFields = form.querySelectorAll('.' + this.config.errorClass);
            errorFields.forEach(field => {
                if (field.classList.contains(this.config.errorClass)) {
                    field.classList.remove(this.config.errorClass);
                }
            });

            const errorMessages = form.querySelectorAll('.' + this.config.errorMessageClass);
            errorMessages.forEach(msg => {
                msg.textContent = '';
                msg.classList.remove('show');
            });
        },

        /**
         * 显示表单级错误信息
         * @method showFormError
         * @param {HTMLElement} form - 表单元素
         * @param {string} message - 错误信息
         */
        showFormError: function(form, message) {
            let formError = form.querySelector('.form-error');
            if (!formError) {
                formError = document.createElement('div');
                formError.className = 'error-message form-error';
                form.insertBefore(formError, form.firstChild);
            }

            formError.textContent = message;
            formError.classList.add('show');
        },

        /**
         * 滚动到第一个错误处
         * @method scrollToFirstError
         * @param {HTMLElement} form - 表单元素
         */
        scrollToFirstError: function(form) {
            const firstError = form.querySelector('.' + this.config.errorClass);
            if (firstError) {
                const element = firstError.tagName === 'INPUT' || firstError.tagName === 'SELECT' ||
                firstError.tagName === 'TEXTAREA' ? firstError :
                    firstError.querySelector('input, select, textarea') || firstError;

                const elementPosition = element.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - this.config.scrollOffset;

                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });

                // 尝试聚焦第一个错误字段
                try {
                    if (element.tagName === 'INPUT' || element.tagName === 'SELECT' || element.tagName === 'TEXTAREA') {
                        element.focus();
                    }
                } catch (e) {
                    console.warn('无法聚焦错误字段:', e);
                }
            }
        },

        /**
         * 添加自定义验证规则
         * @method addRule
         * @param {string} name - 规则名称
         * @param {object} rule - 规则对象 {validate: function, message: string|function}
         */
        addRule: function(name, rule) {
            if (this.rules[name]) {
                console.warn(`规则 "${name}" 已存在，将被覆盖`);
            }
            this.rules[name] = rule;
        },

        /**
         * 设置配置
         * @method setConfig
         * @param {object} config - 配置对象
         */
        setConfig: function(config) {
            Object.assign(this.config, config);
        }
    };

    /**
     * Select操作处理对象
     * 封装所有Select相关的操作和方法
     */
    var SelectHandle = {
        /**
         * 初始化所有自定义Select组件
         * @param {string} [selector='select.custom-select'] - 要初始化的select选择器
         */
        init: function(selector = 'select.custom-select') {
            // 获取所有需要自定义的select元素
            const selects = document.querySelectorAll(selector);

            // 为每个select初始化自定义UI
            selects.forEach(select => {
                this.createCustomUI(select);
            });

            // 添加全局点击事件监听
            this.addGlobalClickHandler();
        },

        /**
         * 为单个select创建自定义UI
         * @param {HTMLSelectElement} select - 原生select元素
         */
        createCustomUI: function(select) {
            // 创建包装容器
            const wrapper = document.createElement('div');
            wrapper.className = 'custom-select-wrapper';

            // 将select放入包装容器
            select.parentNode.insertBefore(wrapper, select);
            wrapper.appendChild(select);

            const isMultiple = select.multiple;
            const isDisabled = select.disabled;

            // 创建显示区域
            const display = this.createDisplayElement(select);

            // 创建下拉箭头
            const arrow = document.createElement('div');
            arrow.className = 'custom-select-arrow';

            // 创建下拉菜单
            const dropdown = this.createDropdownMenu(select);

            // 添加到DOM
            wrapper.appendChild(display);
            wrapper.appendChild(arrow);
            wrapper.appendChild(dropdown);

            // 初始化显示状态
            this.updateDisplayState(select, display);

            // 添加事件监听
            this.bindEvents(select, display, dropdown);

            // 处理禁用状态
            if (isDisabled) {
                display.classList.add('disabled');
            }
        },

        /**
         * 创建自定义显示区域元素
         * @param {HTMLSelectElement} select - 原生select元素
         * @return {HTMLElement} 创建好的显示元素
         */
        createDisplayElement: function(select) {
            const display = document.createElement('div');
            display.className = 'custom-select-display';
            display.tabIndex = select.disabled ? -1 : 0;

            // 如果是多选，添加标签容器
            if (select.multiple) {
                const tagsContainer = document.createElement('div');
                tagsContainer.className = 'custom-select-tags';
                display.appendChild(tagsContainer);
            }

            // 添加文本显示区域
            const content = document.createElement('div');
            display.appendChild(content);

            return display;
        },

        /**
         * 创建下拉菜单
         * @param {HTMLSelectElement} select - 原生select元素
         * @return {HTMLElement} 创建好的下拉菜单
         */
        createDropdownMenu: function(select) {
            const dropdown = document.createElement('div');
            dropdown.className = 'custom-select-dropdown';

            // 添加选项
            Array.from(select.options).forEach(option => {
                const optionElement = document.createElement('div');
                optionElement.className = 'custom-select-option';
                optionElement.textContent = option.text;
                optionElement.dataset.value = option.value;

                // 设置初始选中状态
                if (option.selected) {
                    optionElement.classList.add('selected');
                }

                // 处理禁用状态
                if (option.disabled) {
                    optionElement.classList.add('disabled');
                }

                dropdown.appendChild(optionElement);
            });

            return dropdown;
        },

        /**
         * 更新显示区域的状态
         * @param {HTMLSelectElement} select - 原生select元素
         * @param {HTMLElement} display - 自定义显示元素
         */
        updateDisplayState: function(select, display) {
            const isMultiple = select.multiple;
            const content = display.lastChild;

            if (isMultiple) {
                // 多选模式
                const tagsContainer = display.querySelector('.custom-select-tags');
                tagsContainer.innerHTML = '';

                const selectedOptions = Array.from(select.selectedOptions);

                if (selectedOptions.length > 0) {
                    // 创建选中标签
                    selectedOptions.forEach(option => {
                        const tag = document.createElement('span');
                        tag.className = 'custom-select-tag';
                        tag.innerHTML = `
                                ${option.text}
                                <span class="custom-select-tag-remove" data-value="${option.value}">×</span>
                            `;
                        tagsContainer.appendChild(tag);
                    });
                    content.textContent = '';
                } else {
                    // 无选中项显示占位符
                    content.textContent = '请选择...';
                    content.className = 'custom-select-placeholder';
                }
            } else {
                // 单选模式
                const selectedOption = select.options[select.selectedIndex];
                content.textContent = selectedOption.value ?
                    selectedOption.text : '请选择...';
                content.className = selectedOption.value ?
                    '' : 'custom-select-placeholder';
            }
        },

        /**
         * 绑定事件处理
         * @param {HTMLSelectElement} select - 原生select元素
         * @param {HTMLElement} display - 自定义显示元素
         * @param {HTMLElement} dropdown - 下拉菜单元素
         */
        bindEvents: function(select, display, dropdown) {
            // 点击显示区域切换下拉菜单
            display.addEventListener('click', e => {
                if (select.disabled) return;
                if (e.target.classList.contains('custom-select-tag-remove')) return;

                dropdown.classList.toggle('open');
                display.classList.toggle('open');
            });

            // 点击下拉选项
            dropdown.addEventListener('click', e => {
                const optionElement = e.target.closest('.custom-select-option');
                if (!optionElement || optionElement.classList.contains('disabled')) return;

                const value = optionElement.dataset.value;

                if (select.multiple) {
                    // 多选逻辑
                    const option = select.querySelector(`option[value="${value}"]`);
                    if (option) {
                        option.selected = !option.selected;
                        optionElement.classList.toggle('selected');
                    }
                } else {
                    // 单选逻辑
                    select.value = value;
                    dropdown.querySelectorAll('.custom-select-option').forEach(opt => {
                        opt.classList.remove('selected');
                    });
                    optionElement.classList.add('selected');

                    // 关闭下拉菜单
                    dropdown.classList.remove('open');
                    display.classList.remove('open');
                }

                // 更新显示
                this.updateDisplayState(select, display);

                // 触发change事件
                select.dispatchEvent(new Event('change'));
            });

            // 点击标签删除按钮
            display.addEventListener('click', e => {
                if (e.target.classList.contains('custom-select-tag-remove')) {
                    const value = e.target.dataset.value;
                    const option = select.querySelector(`option[value="${value}"]`);

                    if (option) {
                        option.selected = false;
                        dropdown.querySelector(`.custom-select-option[data-value="${value}"]`)
                            .classList.remove('selected');

                        this.updateDisplayState(select, display);
                        select.dispatchEvent(new Event('change'));
                    }
                }
            });

            // 监听原生select的变化
            select.addEventListener('change', () => {
                this.updateDisplayState(select, display);

                // 更新下拉菜单选中状态
                if (select.multiple) {
                    const selectedValues = Array.from(select.selectedOptions).map(opt => opt.value);
                    dropdown.querySelectorAll('.custom-select-option').forEach(opt => {
                        opt.classList.toggle('selected', selectedValues.includes(opt.dataset.value));
                    });
                } else {
                    dropdown.querySelectorAll('.custom-select-option').forEach(opt => {
                        opt.classList.toggle('selected', opt.dataset.value === select.value);
                    });
                }
            });
        },

        /**
         * 添加全局点击处理，点击外部关闭下拉菜单
         */
        addGlobalClickHandler: function() {
            document.addEventListener('click', e => {
                // 查找所有打开的下拉菜单
                const openDropdowns = document.querySelectorAll('.custom-select-dropdown.open');

                openDropdowns.forEach(dropdown => {
                    // 如果点击的不是自定义select相关的元素
                    const wrapper = dropdown.closest('.custom-select-wrapper');
                    if (!wrapper.contains(e.target)) {
                        dropdown.classList.remove('open');
                        wrapper.querySelector('.custom-select-display').classList.remove('open');
                    }
                });
            });
        },

        /**
         * 销毁自定义Select组件
         * @param {string} [selector='select.custom-select'] - 要销毁的select选择器
         */
        destroy: function(selector = 'select.custom-select') {
            // 移除全局事件监听
            document.removeEventListener('click', this.handleGlobalClick);

            // 恢复原生select并移除自定义UI
            document.querySelectorAll(selector).forEach(select => {
                const wrapper = select.parentElement;

                // 恢复原生select
                select.classList.remove('custom-select');
                select.style.position = '';
                select.style.opacity = '';
                select.style.height = '';
                select.style.width = '';

                // 将select移回原位置
                wrapper.parentNode.insertBefore(select, wrapper);

                // 移除包装容器
                wrapper.remove();
            });
        }
    };

    /**
     * 自定义 方法合集
     */
    var Functions = {
        // 最简单的形式 - 实现 监听点击; eg: myTools.func.click('.test',function(e){...})
        click(element, callback) {
            document.addEventListener("click", e => {
                const clickDom = e.target.closest(element); // 点击元素
                clickDom && typeof callback === "function" && callback(e); // 回调
            }, true);
        },
        // 监听元素的宽高变化
        resizeDom(element = "body", callback = null) {
            if ("ResizeObserver" in window) {
                // 获取 DOM 元素
                var tableElement = document.querySelector(element);
                if (tableElement) {
                    var debounceTimer;
                    // 创建 ResizeObserver 实例
                    var resizeObserver = new ResizeObserver(entries => {
                        for (let entry of entries) {
                            // 清除之前的定时器
                            if (debounceTimer) {
                                clearTimeout(debounceTimer);
                            }
                            // 设置新的定时器，2秒后执行回调
                            debounceTimer = setTimeout(() => {
                                callback && callback(entry.contentRect.width, entry.contentRect.height);
                            }, 1500); // 延迟1.5秒回调
                        }
                    });
                    // 观察 DataTables 表格元素
                    resizeObserver.observe(tableElement);
                } else {
                    console.error("未找到元素:", element);
                }
            } else {
                console.error("不支持ResizeObserver");
            }
        },
        // 复制文本
        copyText: function (text,callback=null) {
            if (navigator.clipboard) {
                // 使用 Clipboard API
                navigator.clipboard.writeText(text)
                    .then(() => {
                        console.log("复制成功");
                        callback && callback();
                    })
                    .catch((err) => {
                        console.error("无法复制文本: ", err);
                    });
            } else {
                // 降级到 execCommand 方法
                const textarea = document.createElement("textarea");
                textarea.value = text;
                document.body.appendChild(textarea);
                textarea.select();
                try {
                    document.execCommand("copy");
                    console.log("复制成功");
                    callback && callback();
                } catch (err) {
                    console.error("无法复制文本: ", err);
                }
                document.body.removeChild(textarea);
            }
        },
        /**
         * 图片加载失败处理（防止重复替换）
         * @param {string} defaultImage - 默认图片URL路径
         */
        handleImageFallback: function (defaultImage) {
            if ( !defaultImage) {
                console.error("必须提供默认图片URL");
                return;
            }

            // 处理单个图片
            const processImage = (img) => {
                // 如果已经处理过或者已经是默认图片，则跳过
                if (img.dataset.fallbackProcessed || img.src === defaultImage) {
                    return;
                }

                // 标记为已处理
                img.dataset.fallbackProcessed = "true";

                // 保存原始src
                img.dataset.originalSrc = img.src;

                // 添加错误事件监听（只触发一次）
                const errorHandler = () => {
                    // 移除事件监听，防止重复触发
                    img.removeEventListener("error", errorHandler);

                    // 替换为默认图片
                    img.src = defaultImage;
                };

                img.addEventListener("error", errorHandler);

                // 立即检查可能已经失败的图片
                if (img.complete && img.naturalHeight === 0) {
                    errorHandler();
                }
            };

            // 初始化处理所有现有图片
            const initExistingImages = () => {
                document.querySelectorAll("img").forEach(processImage);
            };

            // 监听动态添加的图片
            const setupObserver = () => {
                const observer = new MutationObserver((mutations) => {
                    mutations.forEach((mutation) => {
                        mutation.addedNodes.forEach((node) => {
                            if (node.nodeName === "IMG") {
                                processImage(node);
                            } else if (node.querySelectorAll) {
                                node.querySelectorAll("img").forEach(processImage);
                            }
                        });
                    });
                });

                observer.observe(document.body, {
                    childList: true,
                    subtree: true
                });

                return observer;
            };

            // 主初始化
            initExistingImages();
            return setupObserver();
        },
        /**
         * 时间 转10位时间戳
         * @param dateString 例如 2020-01-01
         */
        timestamp: function (dateString = "") {
            return dateString ? parseInt((new Date(dateString)).getTime().toString().substr(0, 10)) : parseInt((new Date()).getTime().toString().substr(0, 10));
        },
        /**
         * 时间格式化 10位时间戳 转时间格式 Y-m-d H:i
         * 10位的 时间戳转时间格式
         */
        timestampToDate: function (timestamp, format = "y-m-d") {
            var date_str = timestamp ? parseInt(timestamp.toString().substr(0, 10)) : parseInt((new Date()).getTime().toString().substr(0, 10));
            var date = new Date(date_str * 1 * 1000);
            format = format ? format : "y-m-d";
            var year = date.getFullYear().toString();
            var month = date.getMonth() * 1 + 1;
            var day = date.getDate();
            var hour = date.getHours();
            var minute = date.getMinutes();
            var second = date.getSeconds();
            var resutl_time = format;
            if (format.indexOf("y") > - 1) {
                resutl_time = resutl_time.replace("y", year);
            }
            if (format.indexOf("m") > - 1) {
                resutl_time = resutl_time.replace("m", (month > 9 ? month : "0" + month).toString());
            }
            if (format.indexOf("d") > - 1) {
                resutl_time = resutl_time.replace("d", (day > 9 ? day : "0" + day).toString());
            }
            if (format.indexOf("h") > - 1) {
                resutl_time = resutl_time.replace("h", (hour > 9 ? hour : "0" + hour).toString());
            }
            if (format.indexOf("i") > - 1) {
                resutl_time = resutl_time.replace("i", (minute > 9 ? minute : "0" + minute).toString());
            }
            if (format.indexOf("s") > - 1) {
                resutl_time = resutl_time.replace("s", (second > 9 ? second : "0" + second).toString());
            }
            return resutl_time;
        },
        /**
         * 高性能 JSON 字符串格式化函数（支持字符串输入）
         * @param {Object|String} input - 要格式化的 JSON 对象或字符串
         * @param {number} [indent=2] - 缩进空格数 (0 表示压缩格式)
         * @returns {String} 格式化后的 JSON 字符串
         */
        formatJson: function (input, indent = 2) {
            // 预计算常量
            const SPACE = indent > 0 ? " ".repeat(indent) : "";
            const NEWLINE = indent > 0 ? "\n" : "";
            const COLON = indent > 0 ? ": " : ":";

            // 状态变量
            let level = 0;
            const stack = [];
            const buffer = [];

            // 工具函数
            const push = str => buffer.push(str);
            const newLine = () => indent && push(NEWLINE + SPACE.repeat(level));

            try {
                // 统一处理输入（支持字符串和对象）
                const obj = typeof input === "string"
                    ? JSON.parse(input.replace(/'/g, "\""))  // 处理单引号JSON
                    : input;

                const format = (value) => {
                    // 基本类型处理
                    if (value === null) return push("null");
                    const type = typeof value;
                    if (type === "boolean") return push(value ? "true" : "false");
                    if (type === "number") return push(Number.isFinite(value) ? String(value) : "null");
                    if (type === "string") return push(JSON.stringify(value));
                    if (type !== "object") return push("null");

                    // 循环引用检测
                    if (stack.includes(value)) throw new Error("Circular reference");
                    stack.push(value);

                    // 处理数组和对象
                    const isArray = Array.isArray(value);
                    push(isArray ? "[" : "{");

                    const entries = isArray ? value : Object.entries(value);
                    if (entries.length > 0) {
                        level ++;
                        for (let i = 0; i < entries.length; i ++) {
                            newLine();
                            // 处理键（仅对象需要）
                            if ( !isArray) {
                                push(`"${entries[i][0]}"${COLON}`);
                            }
                            // 递归处理值
                            format(isArray ? entries[i] : entries[i][1]);

                            // 添加逗号（最后一个元素不加）
                            if (i < entries.length - 1) {
                                push(",");
                            }
                        }
                        level --;
                        newLine();
                    }
                    push(isArray ? "]" : "}");
                    stack.pop();
                };
                format(obj);
                return buffer.join("");
            } catch (error) {
                throw new Error(`JSON 格式化失败: ${error.message}`);
            }
        },
        /**
         * 判断一个对象的类型
         * @returns eg:Array,Object,String,Number,Boolean,Function,Symbol,undefined,null
         * @param data
         */
        getType: function (data) {
            let type = typeof data;
            if (type !== "object") {
                return type;
            }
            return Object.prototype.toString.call(data).replace(/^\[object (\S+)\]$/, "$1");
        },
        //判断变量是否为空的方法
        isEmpty: function (value) {
            if (value == null) return true;

            switch (true) {
                case typeof value === "string":
                    return value.trim() === "";
                case typeof value === "number":
                    return Number.isNaN(value) || value === 0;
                case Array.isArray(value):
                    return value.length === 0;
                case typeof value === "object":
                    if (value instanceof Date) return false;
                    if (value instanceof Map || value instanceof Set) return value.size === 0;
                    return Object.keys(value).length === 0;
                default:
                    return false;
            }
        },
        /**
         * 判断是否为JSON字符串或对象
         * @method is_json
         * @param {*} value - 要检查的值
         * @return {boolean} 是否为JSON
         */
        is_json: function(value) {
            if (typeof value === 'object') return true;
            try {
                JSON.parse(value);
                return true;
            } catch (e) {
                return false;
            }
        },
        /**
         * 判断是否为函数
         * @param string
         * @returns {boolean}
         */
        is_function: function (string) {
            return typeof string === 'function' || (window.hasOwnProperty(string) && typeof window[string] === 'function');
        },
        /**
         * 去除html 标签
         * @return   {[type]}              [description]
         * @param str
         */
        delhtmltag: function (str) {
            try {
                str = str ? str.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, "") : ""; //忽略大小写的正则
                str = str ? str.replace(/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/gi, "") : ""; //忽略大小写的正则
                // str =  str ? str.replace(/<\/?.+?>/g, "") : '';
                str = str ? $(str).text() : "";
            } catch (err) {
            }
            return str;
        },
        inArray: function (str, array) {
            // return array.indexOf(str);
            return array.includes(str);
        },
        /**
         * 获取csrf-token
         * @return {[type]} [description]
         */
        getCsrfToken: function () {
            const meta = document.querySelector("meta[name=\"csrf-token\"]");
            return meta ? meta.getAttribute("content") : null;
        },
        /**
         * 防抖函数
         * 防抖函数的基本思想是：在事件被触发后，等待一段时间再执行回调函数。如果在这段时间内事件又被触发，则重新计时。
         *
         * @param {Function} func 需要防抖的函数
         * @param {number} wait 等待时间(毫秒)
         * @param {boolean} immediate 是否立即执行
         * @return {Function} 返回防抖处理后的函数
         *
         * 使用示例：
         * // 1、基本使用
         * const search = debounce(function(e) { console.log('搜索:', e.target.value); }, 500);
         * input.addEventListener('input', search);
         *
         * // 2、立即执行
         * debounce(function() {  console.log('表单提交'); }, 1000, true);
         *
         * // 3、取消功能
         * const debouncedFn = debounce(() => { console.log('执行了'); }, 1000);
         * // 某些条件下取消
         * setTimeout(() => { debouncedFn.cancel(); }, 500);
         */
        debounce: function (func, wait, immediate) {
            let timeout, result;

            const debounced = function () {
                const context = this;
                const args = arguments;

                if (timeout) clearTimeout(timeout);

                if (immediate) {
                    // 如果已经执行过，不再执行
                    const callNow = !timeout;
                    timeout = setTimeout(() => {
                        timeout = null;
                    }, wait);
                    if (callNow) result = func.apply(context, args);
                } else {
                    timeout = setTimeout(() => {
                        func.apply(context, args);
                    }, wait);
                }

                return result;
            };

            // 取消功能
            debounced.cancel = function () {
                clearTimeout(timeout);
                timeout = null;
            };

            return debounced;
        },
        /**
         * 节流函数
         * 节流函数的基本思想是：在一定时间内，函数最多执行一次。即使在这段时间内事件被多次触发，函数也只会执行一次。
         *
         * @param {Function} func 需要节流的函数
         * @param {number} wait 节流时间(毫秒)
         * @param {Object} options 配置选项
         *        - leading: 是否在节流开始前调用 (默认true)
         *        - trailing: 是否在节流结束后调用 (默认true)
         * @return {Function} 返回节流处理后的函数
         *
         * 使用示例：
         * 1、基本使用
         * window.addEventListener('scroll', throttle(function() {
         *   console.log('滚动事件');
         * }, 200));
         *
         * 2、禁用第一次立即执行
         * const throttledFn = throttle(function() {
         *   console.log('鼠标移动');
         * }, 300, { leading: false });
         * document.addEventListener('mousemove', throttledFn);
         *
         * 3、禁用最后一次执行
         * const throttledFn2 = throttle(function() {
         *   console.log('调整窗口大小');
         * }, 500, { trailing: false });
         * window.addEventListener('resize', throttledFn2);
         *
         * 4、取消功能
         * const tFn = throttle(() => { console.log('执行节流函数'); }, 1000);
         * // 某些条件下取消
         * setTimeout(() => { tFn.cancel(); }, 500);
         */
        throttle: function (func, wait, options) {
            let timeout, context, args, result;
            let previous = 0;
            if ( !options) options = {};

            const later = function () {
                previous = options.leading === false ? 0 : Date.now();
                timeout = null;
                result = func.apply(context, args);
                if ( !timeout) context = args = null;
            };

            const throttled = function () {
                const now = Date.now();
                if ( !previous && options.leading === false) previous = now;
                const remaining = wait - (now - previous);
                context = this;
                args = arguments;

                if (remaining <= 0 || remaining > wait) {
                    if (timeout) {
                        clearTimeout(timeout);
                        timeout = null;
                    }
                    previous = now;
                    result = func.apply(context, args);
                    if ( !timeout) context = args = null;
                } else if ( !timeout && options.trailing !== false) {
                    timeout = setTimeout(later, remaining);
                }

                return result;
            };

            // 取消功能
            throttled.cancel = function () {
                clearTimeout(timeout);
                previous = 0;
                timeout = context = args = null;
            };

            return throttled;
        },
        //获取url中的参数
        urlQuery: function (name, location, get_last) {
            get_last = get_last ? get_last : true; //是否获取地址里面最后一次出现的参数值
            var url = location ? location : window.location.href;
            var splitIndex = url.indexOf("?") + 1;
            var paramStr = url.substr(splitIndex, url.length);
            var arr = paramStr.split("&");
            var lastVal; //最后一次出现的值 在 get_last 为 true 时候生效
            var allParamData = {};
            for (var i = 0; i < arr.length; i ++) {
                var kv = arr[i].split("=");
                if (name) {
                    if (kv[0] === name) {
                        if (get_last) {
                            lastVal = kv[1];
                        } else {
                            return kv[1];
                        }
                    }
                } else {
                    if (lastVal === undefined) {
                        lastVal = {};
                    }
                    //所有
                    lastVal[kv[0]] = kv[1];
                }
            }
            return lastVal;
        },
        // 批量通过 options把 string 中的参数替换
        replaceString: function (string, options = {}) {
            if (myTools.func.isEmpty(string)) {
                return string;
            }
            for (var key in options) {
                string = string.replace(new RegExp("{" + key + "}", "g"), options[key]);
            }
            return string;
        },
        // 获取指定锚点的值
        getAnchorPoint: function (name) {
            var getTab = window.location.hash;
            var args = getTab.substring(1).split("&");
            for (var i = 0, len = args.length; i < len; i ++) {
                var hashItem = args[i];
                var item = hashItem.split("=");
                if (item["0"] === name) {
                    return item["1"] || "";
                }
            }
            return "";
        },
        /**
         * 移除HTML中的指定元素
         * @param htmlString HTML字符串
         * @param domName DOM名称 (例如：#id,.class)
         * @returns {string}
         */
        removeHtmlDom: function (htmlString,domName) {
            // 创建一个新的 DOMParser 实例
            const parser = new DOMParser();
            // 解析 HTML 字符串
            const doc = parser.parseFromString(htmlString, 'text/html');

            // 查找所有的 .copy-code-btn 元素
            const copyCodeBtns = doc.querySelectorAll(domName);

            // 遍历并移除这些元素
            copyCodeBtns.forEach(btn => {
                btn.parentNode.removeChild(btn);
            });

            // 返回处理后的 HTML 字符串
            return doc.body.innerHTML;
        },
        // 获取单选框radio的值
        getRadioValue(name,form=null) {
            const selected = (form === null ? document : form).querySelector(`input[name="${name}"]:checked`);
            return selected ? selected.value : null;
        },
        // 获取复选框checkbox的值
        getCheckboxValue(name,form=null) {
            const selected = (form === null ? document : form).querySelectorAll(`input[name="${name}"]:checked`);
            return Array.from(selected).map(item => item.value);
        },
    };

    /**
     * 强大的HTTP请求封装对象
     * 支持GET、POST、PUT、DELETE等所有HTTP方法
     * 支持文件上传和下载
     * 支持请求/响应拦截器
     * 支持全局配置和实例隔离
     */
    var HttpRequest = {
        // 默认配置项
        defaults: {
            baseURL: '', // 基础URL
            timeout: 10000, // 超时时间(毫秒)
            headers: { // 默认请求头
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            responseType: 'json', // 默认响应类型(json/text/blob等)
            withCredentials: false, // 是否携带跨域凭据
        },

        // 拦截器存储
        interceptors: {
            request: [], // 请求拦截器数组
            response: [], // 响应拦截器数组
            error: function(err) { // 默认错误处理函数
                console.error('HTTP请求错误:', err);
            }
        },

        /**
         * 设置全局配置
         * @param {Object} config 配置对象
         * @returns {HttpRequest} 返回自身用于链式调用
         */
        config: function(config) {
            // 合并默认配置
            Object.assign(this.defaults, config);
            return this;
        },

        /**
         * 添加拦截器
         * @param {'request'|'response'|'error'} type 拦截器类型
         * @param {Function} handler 拦截处理函数
         * @returns {HttpRequest} 返回自身用于链式调用
         */
        intercept: function(type, handler) {
            if (this.interceptors[type] !== undefined) {
                if (type === 'error') {
                    // 错误拦截器直接替换
                    this.interceptors.error = handler;
                } else {
                    // 请求/响应拦截器添加到数组
                    this.interceptors[type].push(handler);
                }
            }
            return this;
        },

        /**
         * 核心请求方法
         * @param {string} method HTTP方法(GET/POST等)
         * @param {string} url 请求URL
         * @param {*} data 请求数据
         * @param {Object} config 请求配置
         * @returns {Promise} 返回Promise对象
         */
        request: function(method, url, data, config={}) {
            // 合并配置
            var options = Object.assign({}, this.defaults, config);

            // 创建AbortController用于超时控制
            var controller = new AbortController();
            var timeoutId = setTimeout(function() {
                controller.abort();
            }, options.timeout);

            // 构建完整URL
            var fullUrl = url.startsWith('http') ? url : options.baseURL + url;

            // 准备请求配置
            var requestConfig = {
                method: method.toUpperCase(),
                headers: new Headers(options.headers),
                signal: controller.signal,
                credentials: options.withCredentials ? 'include' : 'same-origin'
            };

            // 处理请求数据
            if (data) {
                if (method.toUpperCase() === 'GET') {
                    // GET请求将数据转为查询参数
                    var params = new URLSearchParams();
                    for (var key in data) {
                        if (data[key] !== undefined) {
                            params.append(key, data[key]);
                        }
                    }
                    fullUrl += (fullUrl.includes('?') ? '&' : '?') + params.toString();
                } else {
                    // 根据Content-Type处理请求体
                    var contentType = options.headers['Content-Type'] || '';
                    if (contentType.includes('application/json')) {
                        requestConfig.body = JSON.stringify(data);
                    } else if (contentType.includes('multipart/form-data')) {
                        // 文件上传处理
                        var formData = new FormData();
                        for (var key in data) {
                            if (data[key] !== undefined) {
                                formData.append(key, data[key]);
                            }
                        }
                        requestConfig.body = formData;
                    } else if (contentType.includes('application/x-www-form-urlencoded')) {
                        var urlEncoded = new URLSearchParams();
                        for (var key in data) {
                            if (data[key] !== undefined) {
                                urlEncoded.append(key, data[key]);
                            }
                        }
                        requestConfig.body = urlEncoded;
                    } else {
                        requestConfig.body = data;
                    }
                }
            }

            // 执行请求拦截器
            var self = this;
            return Promise.resolve()
                .then(function() {
                    // 依次执行所有请求拦截器
                    return self.interceptors.request.reduce(function(chain, interceptor) {
                        return chain.then(function(config) {
                            return interceptor(config) || config;
                        });
                    }, Promise.resolve({
                        url: fullUrl,
                        ...requestConfig
                    }));
                })
                .then(function(finalConfig) {
                    // 提取最终的url和config
                    fullUrl = finalConfig.url;
                    delete finalConfig.url;
                    requestConfig = finalConfig;

                    // 发起fetch请求
                    return fetch(fullUrl, requestConfig)
                        .finally(function() {
                            // 清除超时定时器
                            clearTimeout(timeoutId);
                        });
                })
                .then(function(response) {
                    // 执行响应拦截器
                    return self.interceptors.response.reduce(function(chain, interceptor) {
                        return chain.then(function(res) {
                            return interceptor(res.clone()) || res;
                        });
                    }, Promise.resolve(response));
                })
                .then(function(response) {
                    // 检查响应状态
                    if (!response.ok) {
                        // 尝试读取响应内容并打印
                        return response.text().then(text => {
                            var error;
                            // 判断 text 是否为 JSON
                            if(Functions.is_json(text)){
                                let res = JSON.parse(text);
                                error =  new Error(response.status+ ':' + (res.message || res.msg || '请求失败'));
                                error.response = response;
                                throw error;
                            }else{
                                error = new Error('请求失败: ' + response.status);
                                error.response = response;
                                throw error;
                            }
                        });
                    }

                    // 根据responseType处理响应数据
                    switch (options.responseType) {
                        case 'json':
                            return response.json();
                        case 'text':
                            return response.text();
                        case 'blob':
                            return response.blob();
                        case 'formData':
                            return response.formData();
                        case 'arrayBuffer':
                            return response.arrayBuffer();
                        default:
                            return response;
                    }
                })
                .catch(function(error) {
                    // 执行错误拦截器
                    self.interceptors.error(error);
                    throw error;
                });
        },

        /**
         * 文件下载方法
         * @param {string} url 下载URL
         * @param {string} filename 下载保存的文件名
         * @param {Object} config 请求配置
         * @returns {Promise} 返回Promise对象
         */
        download: function (url, filename, config) {
            // 设置响应类型为blob
            var downloadConfig = Object.assign({}, config, {
                responseType: 'blob'
            });

            return this.get(url, null, downloadConfig)
                .then(function(blob) {
                    // 创建下载链接
                    var a = document.createElement('a');
                    var url = window.URL.createObjectURL(blob);
                    a.href = url;
                    a.download = filename || 'download';
                    document.body.appendChild(a);
                    a.click();

                    // 清理
                    setTimeout(function() {
                        document.body.removeChild(a);
                        window.URL.revokeObjectURL(url);
                    }, 100);
                });
        },

        /**
         * 文件上传方法(简化版)
         * @param {string} url 上传URL
         * @param {Object} formData 表单数据(包含文件)
         * @param {Object} config 请求配置
         * @returns {Promise} 返回Promise对象
         */
        upload : function(url, formData, config) {
            // 设置Content-Type为multipart/form-data
            var uploadConfig = Object.assign({}, config, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            });

            return this.post(url, formData, uploadConfig);
        },

        /**
         * 创建独立的HttpRequest实例
         * @param {Object} config 实例配置
         * @returns {Object} 返回新的HttpRequest实例
         */
        create: function(config) {
            // 创建新实例
            var instance = Object.create(this);

            // 复制当前配置
            instance.defaults = Object.assign({}, this.defaults, config);
            instance.interceptors = {
                request: [...this.interceptors.request],
                response: [...this.interceptors.response],
                error: this.interceptors.error
            };

            return instance;
        }
    };
    // 为HttpRequest添加快捷方法(GET, POST, PUT, DELETE等)
    ['get', 'post', 'put', 'delete', 'patch', 'head'].forEach(function(method) {
        HttpRequest[method] = function(url, data, config) {
            return this.request(method, url, data, config);
        }.bind(HttpRequest);
    });

    /**
     * 极致优化版 Tips 气泡提示
     * 功能特点：
     * 1. 极简API：仅使用 data-tips 控制位置，title 控制内容
     * 2. 智能内容识别：自动识别普通文本、HTML内容和DOM元素
     * 3. 五种定位方式：data-tips 支持 top, bottom, left, right, track(跟随鼠标)
     * 3. 气泡内容：title 支持普通文本/HTML/DOM(#id,.class)引用三种内容类型
     * 4. 自动边界检测：防止提示框超出视口
     * 5. 平滑动画效果：CSS3过渡动画
     * 6. 性能优化：事件委托+防抖处理
     * 7. 动态元素支持：自动处理新增DOM元素
     *
     * 使用示例：
     * <!-- 普通文本提示 -->
     * <button title="这是提示内容" data-tips="top">上侧提示</button>
     *
     * <!-- HTML内容提示 -->
     * <button title="<strong>加粗提示</strong>" data-tips="bottom">下侧提示</button>
     *
     * <!-- DOM元素内容查找获取并提示 -->
     * <button title="#contentId" data-tips="left">左侧提示</button>
     *
     * <!-- 跟随鼠标提示 -->
     * <button title="跟随提示" data-tips="track">跟随提示</button>
     */
    var Tips = {
        // 配置参数
        config: {
            className: 'tips-container',      // 气泡类名
            arrowClassName: 'tips-arrow',     // 箭头类名
            allowTypes: ['top', 'bottom', 'left', 'right', 'track'], // 允许的定位类型
            defaultType: 'track',            // 默认定位类型
            showDelay: 60,                   // 显示延迟(ms)
            hideDelay: 150,                  // 隐藏延迟(ms)
            offset: 12,                      // 静态定位偏移量
            trackOffset: { x: 0, y: 15 },    // 跟随定位偏移量
            maxWidth: 300,                   // 最大宽度
            arrowSize: 8,                    // 箭头大小
            checkViewport: true              // 是否检查视口边界
        },

        // 状态变量
        tipElement: null,        // 气泡元素
        tipArrow: null,         // 箭头元素
        currentTarget: null,     // 当前触发元素
        showTimeout: null,      // 显示定时器
        hideTimeout: null,      // 隐藏定时器
        lastPosition: null,     // 最后鼠标位置
        scrollListener: null,   // 滚动监听器
        resizeListener: null,   // 窗口大小变化监听器

        /**
         * 初始化方法
         * options：支持修改的配置参数
         */
        init: function(options) {
            // 合并配置
            if (options) {
                for (var key in options) {
                    if (this.config.hasOwnProperty(key)) {
                        this.config[key] = options[key];
                    }
                }
            }

            // 初始化监听器
            this.setupListeners();
            // 绑定事件
            this.setupEventListeners();
        },

        /**
         * 设置全局监听器
         */
        setupListeners: function() {
            // 移除旧的监听器
            if (this.scrollListener) {
                window.removeEventListener('scroll', this.scrollListener);
            }
            if (this.resizeListener) {
                window.removeEventListener('resize', this.resizeListener);
            }

            // 创建新的监听器
            this.scrollListener = this.handleScroll.bind(this);
            this.resizeListener = this.handleResize.bind(this);

            window.addEventListener('scroll', this.scrollListener, { passive: true });
            window.addEventListener('resize', this.resizeListener, { passive: true });
        },

        /**
         * 滚动事件处理
         */
        handleScroll: function() {
            // 如果当前有显示的气泡，则更新位置
            if (this.tipElement && this.tipElement.style.display === 'block') {
                var tipType = this.getCurrentTipType();
                var target = tipType === 'track' ? this.lastPosition : this.currentTarget;
                this.positionTip(target, tipType);
            }
        },

        /**
         * 窗口大小变化处理
         */
        handleResize: function() {
            this.handleScroll();
        },

        /**
         * 获取当前气泡类型
         */
        getCurrentTipType: function() {
            if (!this.tipElement) return this.config.defaultType;

            for (var i = 0; i < this.config.allowTypes.length; i++) {
                if (this.tipElement.classList.contains(this.config.allowTypes[i])) {
                    return this.config.allowTypes[i];
                }
            }

            return this.config.defaultType;
        },

        /**
         * 设置事件监听
         */
        setupEventListeners: function() {
            // 移除旧监听
            this.removeEventListeners();

            // 获取所有提示元素
            var elements = document.querySelectorAll('[data-tips]');

            for (var i = 0; i < elements.length; i++) {
                var el = elements[i];

                // 确保只初始化一次
                if (!el.dataset.tipsInitialized) {
                    // 保存原始title并清空
                    el.dataset.originalTitle = el.title;
                    el.title = '';
                    el.dataset.tipsInitialized = 'true';

                    // 绑定事件
                    el.addEventListener('mouseenter', this.handleMouseEnter.bind(this));
                    el.addEventListener('mouseleave', this.handleMouseLeave.bind(this));
                    el.addEventListener('mousemove', this.handleMouseMove.bind(this));
                }
            }
        },

        /**
         * 移除事件监听
         */
        removeEventListeners: function() {
            var elements = document.querySelectorAll('[data-tips][data-tips-initialized="true"]');
            for (var i = 0; i < elements.length; i++) {
                var el = elements[i];
                el.removeEventListener('mouseenter', this.handleMouseEnter);
                el.removeEventListener('mouseleave', this.handleMouseLeave);
                el.removeEventListener('mousemove', this.handleMouseMove);
            }
        },

        /**
         * 鼠标移入处理
         */
        handleMouseEnter: function(e) {
            this.currentTarget = e.currentTarget;
            clearTimeout(this.hideTimeout);

            // 立即记录鼠标位置
            this.updateLastPosition(e);

            // 延迟显示(防抖)
            this.showTimeout = setTimeout(function() {
                this.showTip(this.currentTarget);
            }.bind(this), this.config.showDelay);
        },

        /**
         * 更新最后鼠标位置
         */
        updateLastPosition: function(e) {
            this.lastPosition = {
                x: e.clientX,
                y: e.clientY,
                scrollX: window.scrollX || document.documentElement.scrollLeft,
                scrollY: window.scrollY || document.documentElement.scrollTop,
                target: e.currentTarget
            };
        },

        /**
         * 鼠标移出处理
         */
        handleMouseLeave: function() {
            clearTimeout(this.showTimeout);
            this.hideTimeout = setTimeout(function() {
                this.hideTip();
            }.bind(this), this.config.hideDelay);
        },

        /**
         * 鼠标移动处理
         */
        handleMouseMove: function(e) {
            // 更新鼠标位置
            this.updateLastPosition(e);

            // 如果是track模式且气泡可见，则更新位置
            if (this.tipElement && this.tipElement.classList.contains('track')) {
                if (this.tipElement.style.display === 'block') {
                    this.positionTip(this.lastPosition, 'track');
                }
            }
        },

        /**
         * 创建气泡元素
         */
        createTipElement: function() {
            // 如果不存在或已被移除则创建
            if (!this.tipElement || !document.body.contains(this.tipElement)) {
                this.tipElement = document.createElement('div');
                this.tipElement.className = this.config.className;

                // 创建箭头元素
                this.tipArrow = document.createElement('div');
                this.tipArrow.className = this.config.arrowClassName;
                this.tipElement.appendChild(this.tipArrow);

                // 设置基础样式
                Object.assign(this.tipElement.style, {
                    position: 'absolute',
                    maxWidth: this.config.maxWidth + 'px',
                    display: 'none',
                    zIndex: '9999',
                    pointerEvents: 'none'
                });

                document.body.appendChild(this.tipElement);
            }
            return this.tipElement;
        },

        /**
         * 显示气泡
         */
        showTip: function(target) {
            var content = target.dataset.originalTitle || '';
            if (!content) return;

            var tipElement = this.createTipElement();
            this.setContent(tipElement, content);

            var tipType = this.getTipType(target);

            // 显示前强制重排确保尺寸正确
            this.forceReflow(tipElement);

            // 定位气泡
            this.positionTip(tipType === 'track' ? this.lastPosition : target, tipType);

            // 显示气泡
            tipElement.classList.add(tipType, 'visible');
            tipElement.style.display = 'block';
        },

        /**
         * 强制重排以获取准确尺寸
         */
        forceReflow: function(element) {
            return element.offsetHeight;
        },

        /**
         * 隐藏气泡
         */
        hideTip: function() {
            if (this.tipElement) {
                this.tipElement.classList.remove('visible');
                // 动画结束后隐藏
                setTimeout(function() {
                    if (this.tipElement && !this.tipElement.classList.contains('visible')) {
                        this.tipElement.style.display = 'none';
                    }
                }.bind(this), this.config.hideDelay);
            }
        },

        /**
         * 设置气泡内容
         */
        setContent: function(element, content) {
            // 保留箭头，移除其他内容
            while (element.childNodes.length > 1) {
                element.removeChild(element.lastChild);
            }

            // 创建内容容器
            var contentWrapper = document.createElement('div');
            contentWrapper.style.margin = '0';
            contentWrapper.style.padding = '0';

            // DOM选择器
            if (/^[.#]/.test(content)) {
                var targetEl = document.querySelector(content);
                if (targetEl) {
                    // 克隆并显示内容
                    var clone = targetEl.cloneNode(true);
                    clone.style.display = 'block';
                    clone.style.visibility = 'visible';
                    contentWrapper.appendChild(clone);
                }
            }
            // HTML内容
            else if (/^</.test(content)) {
                contentWrapper.innerHTML = content;
            }
            // 普通文本
            else {
                // 处理换行符
                var textContent = content.replace(/\n/g, ' ').replace(/\\n/g, '\n');
                var lines = textContent.split('\n');

                for (var i = 0; i < lines.length; i++) {
                    if (i > 0) contentWrapper.appendChild(document.createElement('br'));
                    contentWrapper.appendChild(document.createTextNode(lines[i]));
                }
            }

            element.appendChild(contentWrapper);
        },

        /**
         * 获取定位类型
         */
        getTipType: function(element) {
            var type = (element.dataset.tips || '').toLowerCase();
            return this.config.allowTypes.includes(type) ? type : this.config.defaultType;
        },

        /**
         * 定位气泡(核心方法)
         */
        positionTip: function(target, type) {
            if (!this.tipElement) return;

            // 重置位置类
            this.config.allowTypes.forEach(function(t) {
                this.tipElement.classList.remove(t);
            }, this);

            this.tipElement.classList.add(type);

            // 跟随鼠标定位
            if (type === 'track') {
                this.positionTrackTip(target);
            }
            // 静态元素定位
            else {
                this.positionStaticTip(target, type);
            }
        },

        /**
         * 静态元素定位
         */
        positionStaticTip: function(element, type) {
            // 获取精确尺寸
            var tipRect = this.tipElement.getBoundingClientRect();
            var targetRect = element.getBoundingClientRect();
            var scrollX = window.scrollX || document.documentElement.scrollLeft;
            var scrollY = window.scrollY || document.documentElement.scrollTop;

            var top = 0, left = 0;

            // 计算基础位置
            switch (type) {
                case 'top':
                    top = targetRect.top + scrollY - tipRect.height - this.config.offset;
                    left = targetRect.left + scrollX + targetRect.width / 2 - tipRect.width / 2;
                    break;
                case 'bottom':
                    top = targetRect.top + scrollY + targetRect.height + this.config.offset;
                    left = targetRect.left + scrollX + targetRect.width / 2 - tipRect.width / 2;
                    break;
                case 'left':
                    top = targetRect.top + scrollY + targetRect.height / 2 - tipRect.height / 2;
                    left = targetRect.left + scrollX - tipRect.width - this.config.offset;
                    break;
                case 'right':
                    top = targetRect.top + scrollY + targetRect.height / 2 - tipRect.height / 2;
                    left = targetRect.left + scrollX + targetRect.width + this.config.offset;
                    break;
            }

            // 视口边界检查
            if (this.config.checkViewport) {
                var viewportWidth = window.innerWidth;
                var viewportHeight = window.innerHeight;

                // 水平边界
                left = Math.max(0, Math.min(left, viewportWidth + scrollX - tipRect.width));
                // 垂直边界
                top = Math.max(0, Math.min(top, viewportHeight + scrollY - tipRect.height));
            }

            // 应用最终位置
            this.tipElement.style.left = Math.round(left) + 'px';
            this.tipElement.style.top = Math.round(top) + 'px';
        },

        /**
         * 跟随鼠标定位
         */
        positionTrackTip: function(position) {
            // 确保气泡已渲染并获取准确尺寸
            this.tipElement.style.display = 'block';
            var tipRect = this.tipElement.getBoundingClientRect();

            // 计算位置(考虑滚动和偏移)
            var left = position.x + this.config.trackOffset.x;
            var top = position.y + this.config.trackOffset.y;

            // 视口边界检查
            if (this.config.checkViewport) {
                var viewportWidth = window.innerWidth;
                var viewportHeight = window.innerHeight;

                // 水平边界
                left = Math.max(0, Math.min(
                    left,
                    viewportWidth - tipRect.width
                ));

                // 垂直边界
                top = Math.max(0, Math.min(
                    top,
                    viewportHeight - tipRect.height
                ));
            }

            // 应用位置(添加滚动偏移)
            this.tipElement.style.left = Math.round(left + (position.scrollX || 0)) + 'px';
            this.tipElement.style.top = Math.round(top + (position.scrollY || 0)) + 'px';
        },

        /**
         * 更新提示(用于动态内容)
         */
        update: function() {
            this.setupEventListeners();
        },

        /**
         * 销毁实例
         */
        destroy: function() {
            // 移除事件监听
            this.removeEventListeners();
            if (this.scrollListener) {
                window.removeEventListener('scroll', this.scrollListener);
            }
            if (this.resizeListener) {
                window.removeEventListener('resize', this.resizeListener);
            }

            // 移除气泡
            if (this.tipElement && document.body.contains(this.tipElement)) {
                document.body.removeChild(this.tipElement);
            }

            // 恢复原始title
            var elements = document.querySelectorAll('[data-tips]');
            for (var i = 0; i < elements.length; i++) {
                var el = elements[i];
                if (el.dataset.originalTitle) {
                    el.title = el.dataset.originalTitle;
                    delete el.dataset.originalTitle;
                    delete el.dataset.tipsInitialized;
                }
            }

            // 重置状态
            this.tipElement = null;
            this.tipArrow = null;
            this.currentTarget = null;
            this.lastPosition = null;
        }
    };

    /**
     * 页面加载监听器
     * 功能：
     * 1. DOM加载完成（dom） myTools.load.on('dom', function() {
     *     console.log('DOM加载完成');
     * });
     * 2. DOM和JS加载完成（all） myTools.load.on('all', function() {
     *     console.log('DOM和JS全部加载完成');
     * });
     * 3. 动态内容变化监听（dynamic） myTools.load.on('dynamic', function(mutations) {
     *     console.log('检测到DOM变化:', mutations);
     * });
     * 4. 链式调用  myTools.load.on('dom', function() {
     *         console.log('DOM加载完成');
     *     }).on('all', function() {
     *         console.log('全部资源加载完成');
     *     });
     * 5. 不需要时销毁监听器
     * myTools.load.destroy();
     *
     * 6. 监听dom尺寸变化
     * myTools.load.onResize('body',function() {
     *     console.log('body尺寸发生变化');
     * });
     */
    var onPageLoad = {
        // 存储回调函数
        _callbacks: {
            dom: [],
            all: [],
            dynamic: null
        },

        // 存储MutationObserver实例
        _observer: null,

        // 页面状态标记
        _loaded: {
            dom: false,
            all: false
        },

        /**
         * 链式加载监听方法
         * @param {string} type 监听类型（dom/all/dynamic）
         * @param {function} callback 回调函数
         * @return {object} 返回自身以支持链式调用
         */
        on: function(type, callback) {
            // 参数处理：支持省略type直接传callback
            if (typeof type === 'function') {
                callback = type;
                type = 'dom';
            }

            // 初始化事件监听（惰性初始化）
            if (!this._initialized) {
                this._init();
                this._initialized = true;
            }

            // 注册回调
            this._register(type, callback);

            return this;
        },
        // 监听dom尺寸改变
        onResize: function(element = "body", callback) {
            Functions.resizeDom(element,callback);
            return this;
        },

        /**
         * 内部注册方法
         * @param {string} type 监听类型
         * @param {function} callback 回调函数
         */
        _register: function(type, callback) {
            // 如果已经加载完成，立即执行回调
            if (this._loaded[type]) {
                callback();
                return;
            }

            // 添加到回调列表
            if (type === 'dynamic') {
                this.watchDOM(callback);
                this._callbacks.dynamic = this._callbacks.dynamic || [];
                this._callbacks.dynamic.push(callback);
            } else {
                this._callbacks[type].push(callback);
            }
        },

        /**
         * 初始化事件监听
         */
        _init: function() {
            // DOM加载监听
            document.addEventListener('DOMContentLoaded', () => {
                this._loaded.dom = true;
                this._trigger('dom');

                // 检查脚本是否全部加载
                this._checkScripts();
            });

            // 页面完全加载监听
            window.addEventListener('load', () => {
                this._loaded.all = true;
                this._trigger('all');
            });
        },

        /**
         * DOM变化监听器
         * @param {Function} callback - 变化回调函数
         * @param {Object} [options] - 配置选项
         * @param {HTMLElement} [options.root=document.body] - 监听的根元素
         * @param {Boolean} [options.childList=true] - 是否观察子节点变化
         * @param {Boolean} [options.attributes=true] - 是否观察属性变化
         * @param {Boolean} [options.subtree=true] - 是否观察子树变化
         * @param {Boolean} [options.characterData=true] - 是否观察文本内容变化
         * @param {Number} [options.debounce=50] - 防抖时间(毫秒)
         * @returns {Function} 返回停止监听的函数
         */
         watchDOM:function(callback, {
            root = document.body,
            childList = true,
            attributes = true,
            subtree = true,
            characterData = true,
            debounce = 50
         } = {}) {
            // 参数校验
            if (typeof callback !== 'function') throw new Error('回调必须是函数');
            if (!(root instanceof Node)) throw new Error('root必须是DOM节点');

            let timer = null;
            const changes = new Map();

            const observer = new MutationObserver(mutations => {
                mutations.forEach(mutation => {
                    // 处理属性变化
                    if (mutation.type === 'attributes') {
                        const target = mutation.target;
                        const attrName = mutation.attributeName;
                        changes.set(target, {
                            type: 'attribute',
                            target: target,
                            attribute: attrName,
                            oldValue: mutation.oldValue,
                            newValue: target.getAttribute(attrName)
                        });
                    }
                    // 处理文本内容变化
                    else if (mutation.type === 'characterData') {
                        const parent = mutation.target.parentNode;
                        changes.set(parent, {
                            type: 'text',
                            target: parent,
                            oldValue: mutation.oldValue,
                            newValue: mutation.target.data
                        });
                    }
                    // 处理子节点变化
                    else if (mutation.type === 'childList') {
                        // 处理被移除的节点
                        mutation.removedNodes.forEach(node => {
                            if (node.nodeType === Node.ELEMENT_NODE) {
                                changes.set(node, {
                                    type: 'removed',
                                    target: node,
                                    oldValue: node.cloneNode(true),
                                    newValue: null
                                });
                            }
                        });

                        // 处理添加的节点
                        mutation.addedNodes.forEach(node => {
                            if (node.nodeType === Node.ELEMENT_NODE) {
                                changes.set(node, {
                                    type: 'added',
                                    target: node,
                                    oldValue: null,
                                    newValue: node.cloneNode(true)
                                });
                            }
                        });

                        // 处理父节点变化
                        if (mutation.target.nodeType === Node.ELEMENT_NODE) {
                            changes.set(mutation.target, {
                                type: 'parent',
                                target: mutation.target,
                                oldValue: mutation.previousSibling,
                                newValue: mutation.nextSibling
                            });
                        }
                    }
                });

                // 防抖处理
                clearTimeout(timer);
                timer = setTimeout(() => {
                    if (changes.size > 0) {
                        callback(Array.from(changes.values()));
                        changes.clear();
                    }
                }, debounce);
            });

            // 配置观察选项
            const observerConfig = {
                childList,
                attributes,
                attributeOldValue: attributes,
                subtree,
                characterData,
                characterDataOldValue: characterData
            };

            // 开始观察
            observer.observe(root, observerConfig);

            // 返回停止函数
            return () => {
                clearTimeout(timer);
                observer.disconnect();
            };
        },

        /**
         * 检查脚本加载状态
         */
        _checkScripts: function() {
            var scripts = document.querySelectorAll('script[src]');
            var remaining = scripts.length;

            if (remaining === 0) {
                this._loaded.all = true;
                this._trigger('all');
                return;
            }

            scripts.forEach(script => {
                if (script.readyState === 'loaded' || script.readyState === 'complete') {
                    if (--remaining === 0) this._allScriptsLoaded();
                } else {
                    script.addEventListener('load', () => {
                        if (--remaining === 0) this._allScriptsLoaded();
                    });
                    script.addEventListener('error', () => {
                        if (--remaining === 0) this._allScriptsLoaded();
                    });
                }
            });
        },

        /**
         * 所有脚本加载完成处理
         */
        _allScriptsLoaded: function() {
            this._loaded.all = true;
            this._trigger('all');
        },

        /**
         * 触发指定类型的回调
         * @param {string} type 资源类型
         */
        _trigger: function(type) {
            this._callbacks[type].forEach(fn => {
                try { fn(); } catch(e) { console.error(e); }
            });
            this._callbacks[type] = [];
        },

        /**
         * 销毁方法
         */
        destroy: function() {
            if (this._observer) {
                this._observer.disconnect();
                this._observer = null;
            }
            this._callbacks = { dom: [], all: [], dynamic: null };
            this._loaded = { dom: false, all: false };
        }
    };

    /**
     * 消息提示
     *
     * const msgId = Message.msg("这是一条消息", 3);
     * msgId.close(); // 可以手动关闭
     */
    const Message = {
        _id: 0,
        msg: function(msg, timer = 3.5) {
            // 移除已有的消息框
            const existingMsgBox = document.querySelector('.my-layer-msg-box');
            if (existingMsgBox) {
                existingMsgBox.remove();
            }

            // 设置默认消息
            msg = msg || "操作成功";

            // 生成随机ID
            const random_id = Math.floor(Math.random() * 999999999999) + 1000000000000;

            // 创建消息元素
            const msgBox = document.createElement('div');
            msgBox.className = 'my-layer-msg-box my_layer';
            msgBox.id = 'my_layer_' + random_id;
            msgBox.textContent = msg;

            // 设置样式
            Object.assign(msgBox.style, {
                position: 'fixed',
                top: '50%',
                left: '50%',
                transform: 'translate(-50%, -50%)',
                padding: '5px 15px',
                backgroundColor: 'rgba(0,0,0,.6)',
                color: '#fff',
                border: 'none',
                minWidth: '100px',
                wordBreak: 'break-all',
                textAlign: 'center',
                zIndex: '999',
            });

            // 添加到body
            document.body.appendChild(msgBox);

            // 设置自动关闭
            if (timer > 0) {
                setTimeout(function() {
                    const msgElement = document.getElementById('my_layer_' + random_id);
                    if (msgElement) {
                        msgElement.remove();
                    }
                }, 1000 * timer);
            }
            this._id = random_id;
            return this;
        },
        // 关闭某个消息
        close: function() {
            const msgElement = document.getElementById('my_layer_' + this._id);
            if (msgElement) {
                msgElement.remove();
            }
        },
        // 关闭所有消息
        closeAll: function() {
            const msgElements = document.querySelectorAll('.my_layer');
            for (let i = 0; i < msgElements.length; i++) {
                msgElements[i].remove();
            }
        }
    };

    // 监听事件处理
    var EventHandle = {
        init: function() {
            this.setCopyPreCodeBtn();
        },
        // 设置复制代码块按钮
        setCopyPreCodeBtn: function() {
            const preElements = document.querySelectorAll('pre');

            preElements.forEach(pre => {
                // 检查是否已经添加了复制按钮
                if (pre.querySelector('.copy-code-btn')) {
                    return;
                }
                // 添加复制按钮
                const copyBtn = document.createElement('button');
                copyBtn.className = 'copy-code-btn';
                copyBtn.textContent = '复制';
                copyBtn.title = '复制代码';
                pre.appendChild(copyBtn);

                // 复制功能
                copyBtn.addEventListener('click', () => {
                    let codeString = pre.querySelector('code')?pre.querySelector('code').textContent:pre.textContent;
                    // 去除 codeString 里面的 "复制"
                    codeString = codeString.trimEnd().replace(/([\s\u3000]*复制[\s\u3000]*)$/, '').trimEnd();
                    // 剪贴板复制
                    Functions.copyText(codeString,function (){
                        copyBtn.textContent = '已复制!';
                        copyBtn.classList.add('copied');
                        setTimeout(() => {
                            copyBtn.textContent = '复制';
                            copyBtn.classList.remove('copied');
                        }, 3500);
                    });
                });
            });
            // 查找没有pre 标签包围的code 标签
            const codeElements = document.querySelectorAll('code:not(pre code)');
            codeElements.forEach(codeElement => {
                // 点击 code 标签时复制
                codeElement.addEventListener('click', () => {
                    Functions.copyText(codeElement.textContent);
                })
            })
        },
    };

    /**
     * DOM 操作工具类
     * 提供类似 jQuery 的 DOM 操作和事件处理功能
     */
    var Dom = {
        /**
         * DOM 查询方法
         * @param {string|Element|Array|NodeList} selector - 选择器或DOM元素
         * @param {Element} [context=document] - 查询上下文
         * @returns {Array} 匹配的元素数组
         *
         * @example
         * // ID选择器
         * Dom.query('#main');
         *
         * // 类选择器
         * Dom.query('.item');
         *
         * // 复杂选择器
         * Dom.query('ul li.active');
         *
         * // 从指定上下文查询
         * Dom.query('.item', document.getElementById('container'));
         *
         * // 传入DOM元素
         * Dom.query(document.getElementById('test'));
         *
         * // 传入DOM数组
         * Dom.query([elem1, elem2]);
         */
        query: function(selector, context) {
            // 处理空选择器
            if (!selector) {
                return [];
            }

            context = context || document;

            // 处理DOM元素输入
            if (selector.nodeType === 1) {
                return [selector];
            }

            // 处理DOM元素数组或NodeList
            if (Array.isArray(selector) || selector instanceof NodeList) {
                return Array.from(selector);
            }

            // 处理字符串选择器
            if (typeof selector === 'string') {
                // 优化ID选择器
                if (selector[0] === '#' && !selector.match(/[ .<>:~]/)) {
                    const el = context.getElementById(selector.slice(1));
                    return el ? [el] : [];
                }

                // 通用选择器查询
                try {
                    return Array.from(context.querySelectorAll(selector));
                } catch (e) {
                    console.error(`无效的选择器: ${selector}`);
                    return [];
                }
            }

            console.error('不支持的选择器类型:', selector);
            return [];
        },

        /**
         * 绑定事件处理函数
         * @param {string|Element|Array|NodeList} element - 目标元素或选择器
         * @param {string} events - 事件名称（多个事件用空格分隔）
         * @param {string} [selector] - 委托选择器（可选）
         * @param {Object} [data] - 传递给事件处理函数的额外数据（可选）
         * @param {Function} handler - 事件处理函数
         * @param {boolean} [one=false] - 是否只执行一次（内部使用）
         * @returns {void}
         *
         * @example
         * // 简单点击事件
         * Dom.on('#btn', 'click', function(e) {
         *     console.log('按钮被点击');
         * });
         *
         * // 事件委托
         * Dom.on('#list', 'click', '.item', function(e) {
         *     console.log('列表项被点击', this);
         * });
         *
         * // 传递额外数据
         * Dom.on('#btn', 'click', {id: 123}, function(e) {
         *     console.log('按钮ID:', e.data.id);
         * });
         *
         * // 多事件绑定
         * Dom.on('#input', 'focus blur', function(e) {
         *     console.log('输入框焦点变化:', e.type);
         * });
         */
        on: function(element, events, selector, data, handler, one) {
            // 参数重载处理
            if (typeof selector === 'function') {
                handler = selector;
                data = undefined;
                selector = undefined;
            } else if (typeof data === 'function') {
                handler = data;
                data = undefined;
            }

            // 验证处理函数
            if (typeof handler !== 'function') {
                console.error('事件处理函数必须是一个函数');
                return;
            }

            // 处理多个事件
            const eventList = events.split(' ').filter(e => e.trim());
            if (eventList.length === 0) {
                console.error('未提供有效的事件名称');
                return;
            }

            // 获取目标元素
            const elements = this.query(element);
            if (elements.length === 0) {
                console.warn('未找到匹配的元素');
                return;
            }

            // 为每个元素的每个事件绑定处理函数
            elements.forEach(el => {
                eventList.forEach(eventName => {
                    this._addEventHandler(el, eventName, selector, data, handler, one);
                });
            });
        },

        /**
         * 添加事件处理函数（内部方法）
         * @private
         */
        _addEventHandler: function(el, eventName, selector, data, handler, one) {
            // 创建实际执行的处理函数
            const realHandler = function(e) {
                // 处理事件委托
                let target = e.target;
                let currentTarget = el;

                if (selector) {
                    // 查找匹配选择器的元素
                    while (target && target !== currentTarget) {
                        if (target.matches(selector)) {
                            currentTarget = target;
                            break;
                        }
                        target = target.parentNode;
                    }

                    // 如果没有匹配的元素则返回
                    if (!target || (target === el && !el.matches(selector))) {
                        return;
                    }
                }

                // 添加额外数据到事件对象
                if (data !== undefined) {
                    e.data = data;
                }

                // 执行处理函数，确保this指向当前元素
                const result = handler.call(currentTarget, e);

                // 如果是一次性事件，执行后解绑
                if (one) {
                    this._removeEventHandler(el, eventName, realHandler);
                }

                return result;
            };

            // 存储原始处理函数引用，便于解绑
            realHandler.originalHandler = handler;

            // 存储处理函数以便管理
            const eventKey = `DomEvent_${eventName}`;
            if (!el[eventKey]) {
                el[eventKey] = [];
            }
            el[eventKey].push(realHandler);

            // 绑定事件
            el.addEventListener(eventName, realHandler);
        },

        /**
         * 移除事件处理函数（内部方法）
         * @private
         */
        _removeEventHandler: function(el, eventName, handler) {
            const eventKey = `DomEvent_${eventName}`;
            const handlers = el[eventKey] || [];

            for (let i = handlers.length - 1; i >= 0; i--) {
                const h = handlers[i];
                if (!handler || h === handler || h.originalHandler === handler) {
                    el.removeEventListener(eventName, h);
                    handlers.splice(i, 1);
                }
            }

            if (handlers.length === 0) {
                delete el[eventKey];
            } else {
                el[eventKey] = handlers;
            }
        },

        /**
         * 绑定一次性事件处理函数
         * @param {string|Element|Array|NodeList} element - 目标元素或选择器
         * @param {string} events - 事件名称（多个事件用空格分隔）
         * @param {string} [selector] - 委托选择器（可选）
         * @param {Object} [data] - 传递给事件处理函数的额外数据（可选）
         * @param {Function} handler - 事件处理函数
         * @returns {void}
         *
         * @example
         * // 一次性点击事件
         * Dom.one('#btn', 'click', function() {
         *     console.log('这个只会执行一次');
         * });
         */
        one: function(element, events, selector, data, handler) {
            this.on(element, events, selector, data, handler, true);
        },

        /**
         * 解绑事件处理函数
         * @param {string|Element|Array|NodeList} element - 目标元素或选择器
         * @param {string} [events] - 事件名称（多个事件用空格分隔，不传则解绑所有事件）
         * @param {string|Function} [selector] - 委托选择器或处理函数（可选）
         * @param {Function} [handler] - 要解绑的特定处理函数（可选）
         * @returns {void}
         *
         * @example
         * // 解绑特定处理函数
         * const handler = function() { console.log('点击'); };
         * Dom.on('#btn', 'click', handler);
         * Dom.off('#btn', 'click', handler);
         *
         * // 解绑所有点击事件
         * Dom.off('#btn', 'click');
         *
         * // 解绑所有事件
         * Dom.off('#btn');
         *
         * // 解绑委托事件
         * Dom.off('#list', 'click', '.item');
         */
        off: function(element, events, selector, handler) {
            // 参数重载处理
            if (typeof selector === 'function') {
                handler = selector;
                selector = undefined;
            }

            // 获取目标元素
            const elements = this.query(element);
            if (elements.length === 0) {
                console.warn('未找到匹配的元素');
                return;
            }

            // 确定要解绑的事件列表
            let eventList = [];
            if (events) {
                eventList = events.split(' ').filter(e => e.trim());
            } else {
                // 如果没有指定事件，则解绑元素上的所有事件
                elements.forEach(el => {
                    eventList = eventList.concat(
                        Object.keys(el)
                            .filter(key => key.startsWith('DomEvent_'))
                            .map(key => key.replace('DomEvent_', ''))
                    );
                });
                eventList = [...new Set(eventList)]; // 去重
            }

            if (eventList.length === 0) {
                console.warn('未提供有效的事件名称');
                return;
            }

            // 解绑每个元素的每个事件
            elements.forEach(el => {
                eventList.forEach(eventName => {
                    if (selector) {
                        // 解绑特定委托选择器的事件
                        const eventKey = `DomEvent_${eventName}`;
                        const handlers = el[eventKey] || [];

                        handlers.forEach(h => {
                            if (h.originalHandler === handler ||
                                (handler === undefined && h.selector === selector)) {
                                this._removeEventHandler(el, eventName, h);
                            }
                        });
                    } else {
                        // 解绑普通事件
                        this._removeEventHandler(el, eventName, handler);
                    }
                });
            });
        },

        /**
         * 触发事件
         * @param {string|Element|Array|NodeList} element - 目标元素或选择器
         * @param {string} eventName - 事件名称
         * @param {Object} [extraParameters] - 额外参数（可选）
         * @returns {void}
         *
         * @example
         * // 触发点击事件
         * Dom.trigger('#btn', 'click');
         *
         * // 触发自定义事件并传递数据
         * Dom.trigger('#element', 'customEvent', {detail: '数据'});
         */
        trigger: function(element, eventName, extraParameters) {
            // 获取目标元素
            const elements = this.query(element);
            if (elements.length === 0) {
                console.warn('未找到匹配的元素');
                return;
            }

            if (!eventName) {
                console.error('未提供事件名称');
                return;
            }

            // 为每个元素触发事件
            elements.forEach(el => {
                let event;

                // 创建适当类型的事件对象
                if (typeof CustomEvent === 'function') {
                    // 支持CustomEvent的浏览器
                    event = new CustomEvent(eventName, {
                        bubbles: true,
                        cancelable: true,
                        detail: extraParameters
                    });
                } else if (typeof Event === 'function') {
                    // 支持Event但不支持CustomEvent的浏览器
                    event = new Event(eventName, {
                        bubbles: true,
                        cancelable: true
                    });

                    // 添加额外参数
                    if (extraParameters) {
                        event.detail = extraParameters;
                        Object.assign(event, extraParameters);
                    }
                } else {
                    // 旧版浏览器
                    event = document.createEvent('Event');
                    event.initEvent(eventName, true, true);

                    // 添加额外参数
                    if (extraParameters) {
                        event.detail = extraParameters;
                        for (const key in extraParameters) {
                            if (!event[key]) {
                                event[key] = extraParameters[key];
                            }
                        }
                    }
                }

                // 触发事件
                el.dispatchEvent(event);
            });
        }
    };

    /**
     * 添加常用事件的快捷方法
     * // eg:
     * Dom.click('#btn2', function() {
     *     console.log('Button 2 clicked');
     * });
     */
    ['click', 'dblclick', 'mouseenter', 'mouseleave', 'mouseover', 'mouseout',
     'mousedown', 'mouseup', 'mousemove', 'keydown', 'keypress', 'keyup',
     'submit', 'change', 'focus', 'blur', 'focusin', 'focusout', 'resize',
     'scroll', 'select', 'contextmenu'].forEach(function(eventName) {
        Dom[eventName] = function(element, selector, data, handler) {
            // 参数重载处理
            if (typeof selector === 'function') {
                handler = selector;
                selector = undefined;
                data = undefined;
            } else if (typeof data === 'function') {
                handler = data;
                data = undefined;
            }

            Dom.on(element, eventName, selector, data, handler);
        };
    });

    var myTools = {
        // 表单处理类
        form:FormHandle,
        // 工具类
        func:Functions,
        // http请求类
        http:HttpRequest,
        // 监听页面加载
        load:onPageLoad,
        // 网页 Dom 操作处理类
        dom:Dom,
        // 消息提示
        msg: (msg, timer = 3.5)=>Message.msg(msg, timer),
        // 关闭所有消息
        closeAllMsg:()=>Message.closeAll(),

        /**
         * 初始化方法
         * @method init
         */
        init: function() {
            // 初始化 Form 表单相关操作
            FormHandle.init();
            // 初始化 下拉框相关操作
            SelectHandle.init();
            // 初始化 提示框相关操作
            Tips.init();
            // 监听事件处理
            EventHandle.init();
        },
    };

    // 将Tools暴露到全局
    global.myTools = myTools;

    // 文档加载完成后自动初始化
    // 自动初始化
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            myTools.init();
        });
    } else {
        myTools.init();
    }

    myTools.load.on('dom', function() {
        // console.log('DOM加载完成');
    })

}(window, document);
