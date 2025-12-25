/**
 * Voice Command Processor
 * نظام بسيط لمعالجة الأوامر الصوتية العربية
 */

/**
 * تطبيع الكلمات العربية للبحث المرن
 * يتعامل مع الاختلافات في التاء المربوطة/المفتوحة والهمزة والألف
 * @param {string} text - النص المراد تطبيعه
 * @returns {string} - النص المطبق
 */
function normalizeArabicText(text) {
    if (!text) return '';
    
    let normalized = text.trim();
    
    // تطبيع التاء المربوطة (ة) والتاء المفتوحة (ت) والهاء (ه) في نهاية الكلمة
    // نستبدلها جميعاً بالهاء للبحث المرن
    // نبحث عن التاء المربوطة أو التاء المفتوحة أو الهاء في نهاية الكلمة (قبل مسافة أو نهاية النص)
    normalized = normalized.replace(/ة([\s\u200C\u200D\u200E\u200F\u00A0]|$)/g, 'ه$1');
    normalized = normalized.replace(/ت([\s\u200C\u200D\u200E\u200F\u00A0]|$)/g, 'ه$1');
    
    // تطبيع الهمزة بأشكالها المختلفة (أ، إ، آ، ء) إلى الألف
    normalized = normalized.replace(/[أإآء]/g, 'ا');
    
    // تطبيع الياء والياء المجرورة
    normalized = normalized.replace(/[يى]/g, 'ي');
    
    // إزالة الشدة والحركات (لتحسين البحث)
    normalized = normalized.replace(/[\u064B-\u065F\u0670]/g, '');
    
    return normalized;
}

/**
 * مقارنة مرنة بين نصين عربيين
 * @param {string} text1 - النص الأول
 * @param {string} text2 - النص الثاني
 * @returns {boolean} - true إذا كانا متطابقين بعد التطبيع
 */
function fuzzyMatchArabic(text1, text2) {
    if (!text1 || !text2) return false;
    
    const normalized1 = normalizeArabicText(text1);
    const normalized2 = normalizeArabicText(text2);
    
    // مقارنة مباشرة
    if (normalized1 === normalized2) return true;
    
    // مقارنة case-insensitive
    if (normalized1.toLowerCase() === normalized2.toLowerCase()) return true;
    
    // التحقق من احتواء أحد النصوص للآخر (مفيد للبحث الجزئي)
    if (normalized1.includes(normalized2) || normalized2.includes(normalized1)) return true;
    
    // مقارنة الكلمات (إذا كان النص يحتوي على عدة كلمات)
    const words1 = normalized1.split(/\s+/).filter(w => w.length > 0);
    const words2 = normalized2.split(/\s+/).filter(w => w.length > 0);
    
    // إذا تطابقت الكلمات الرئيسية (لكل كلمة في text2 يجب أن توجد في text1)
    if (words1.length > 0 && words2.length > 0) {
        const allWordsMatch = words2.every(w2 => 
            words1.some(w1 => {
                const w1Lower = w1.toLowerCase();
                const w2Lower = w2.toLowerCase();
                return w1Lower === w2Lower || w1Lower.includes(w2Lower) || w2Lower.includes(w1Lower);
            })
        );
        if (allWordsMatch) return true;
    }
    
    return false;
}

/**
 * استخراج اسم القسم من الأمر الصوتي
 * @param {string} command - الأمر الصوتي
 * @param {Array} departments - قائمة الأقسام
 * @returns {Object|null} - القسم المطابق أو null
 */
export function extractDepartmentFromCommand(command, departments) {
    if (!command || !command.trim()) {
        return null;
    }
    
    // تنظيف الأمر وإزالة الأحرف الزائدة
    // دعم كلا من الإنجليزية والعربية
    let cleanedCommand = command.toLowerCase().trim();
    
    // إزالة علامات الترقيم والأحرف الخاصة (مع الحفاظ على الأحرف العربية)
    // لكن لا نستخدم toLowerCase() على النص العربي لأنه قد يسبب مشاكل
    let normalizedCommand = command.trim()
        .replace(/[^\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\uFB50-\uFDFF\uFE70-\uFEFFa-z0-9\s]/gi, '')
        .trim();
    
    // نسخة بالإنجليزية lowercase للبحث
    const normalizedCommandLower = normalizedCommand.toLowerCase();
    
    console.log('Processing command - Original:', command);
    console.log('Processing command - Normalized:', normalizedCommand);
    console.log('Processing command - Lowercase:', normalizedCommandLower);
    
    // بناء خريطة أسماء الأقسام من البيانات الفعلية للنظام
    // نحاول إنشاء مرادفات إنجليزية بناءً على الأسماء الموجودة فعلياً
    const englishDepartmentMap = {};
    
    // إنشاء خريطة بناءً على أسماء الأقسام الفعلية في النظام
    departments.forEach(dept => {
        const deptNameLower = dept.name.toLowerCase();
        // يمكن إضافة مرادفات إنجليزية شائعة بناءً على الكلمات الرئيسية في الاسم
        // لكن سنعتمد بشكل أساسي على البحث المباشر في الأسماء الفعلية
    });
    
    // تحويل الأسماء الإنجليزية الشائعة (كمرجع مساعد فقط، لكن البحث الأساسي من البيانات)
    const commonEnglishTerms = {
        'bones': 'عظام',
        'orthopedic': 'عظام',
        'internal': 'باطنة',
        'internal medicine': 'باطنة',
        'cardiology': 'قلب',
        'heart': 'قلب',
        'pediatrics': 'أطفال',
        'children': 'أطفال',
        'obstetrics': 'نساء',
        'gynecology': 'نساء',
        'women': 'نساء',
        'eyes': 'عيون',
        'ophthalmology': 'عيون',
        'ent': 'أنف',
        'ear nose throat': 'أنف',
        'dermatology': 'جلدية',
        'skin': 'جلدية',
        'dental': 'أسنان',
        'teeth': 'أسنان',
        'neurology': 'أعصاب',
        'nerves': 'أعصاب',
        'psychiatry': 'نفسية',
        'mental': 'نفسية',
    };
    
    let processedCommand = normalizedCommand;
    let processedCommandLower = normalizedCommandLower;
    
    // أولاً: محاولة مطابقة المصطلحات الإنجليزية مع أسماء الأقسام الفعلية
    for (const [english, arabicTerm] of Object.entries(commonEnglishTerms)) {
        if (normalizedCommandLower.includes(english)) {
            // نبحث عن قسم يحتوي على هذا المصطلح العربي في اسمه
            const matchingDept = departments.find(dept => 
                dept.name.toLowerCase().includes(arabicTerm.toLowerCase())
            );
            if (matchingDept) {
                // نستبدل المصطلح الإنجليزي بالاسم الفعلي للقسم
                processedCommand = processedCommand.replace(new RegExp(english, 'gi'), matchingDept.name);
                processedCommandLower = processedCommandLower.replace(english, matchingDept.name.toLowerCase());
            }
        }
    }
    
    // البحث عن اسم القسم في الأمر
    console.log('Available departments:', departments.map(d => d.name));
    
    for (const dept of departments) {
        // نستخدم الاسم الأصلي للقسم (بدون lowercase) للبحث في النص العربي
        const deptName = dept.name.trim();
        const deptNameLower = deptName.toLowerCase();
        console.log(`Checking department: "${deptName}" (${deptNameLower}) against command`);
        
        // تطبيع الأسماء للبحث المرن
        const normalizedDeptName = normalizeArabicText(deptName);
        const normalizedDeptNameLower = normalizedDeptName.toLowerCase();
        const normalizedCommandNormalized = normalizeArabicText(normalizedCommand);
        const normalizedCommandLowerNormalized = normalizeArabicText(normalizedCommandLower);
        const processedCommandNormalized = normalizeArabicText(processedCommand);
        const processedCommandLowerNormalized = normalizeArabicText(processedCommandLower);
        
        // البحث المباشر باستخدام التطبيع المرن
        if (fuzzyMatchArabic(normalizedCommand, deptName) ||
            fuzzyMatchArabic(normalizedCommandLower, deptNameLower) ||
            fuzzyMatchArabic(processedCommand, deptName) ||
            fuzzyMatchArabic(processedCommandLower, deptNameLower) ||
            normalizedCommandNormalized.includes(normalizedDeptName) ||
            normalizedCommandLowerNormalized.includes(normalizedDeptNameLower) ||
            processedCommandNormalized.includes(normalizedDeptName) ||
            processedCommandLowerNormalized.includes(normalizedDeptNameLower)) {
            console.log('✓ Found department by direct match (fuzzy):', dept.name);
            return dept;
        }
        
        // البحث بجزء من الاسم (للمرونة) - نبحث عن كلمات من 3 أحرف أو أكثر
        // لكن نتجنب الكلمات المفتاحية الشائعة مثل "قسم" لتجنب النتائج الخاطئة
        const commonKeywords = ['قسم', 'عيادة', 'دكاترة', 'دكتور', 'أطباء', 'طبيب'];
        const deptWords = deptName.split(/\s+/).filter(word => {
            const normalizedWord = normalizeArabicText(word.toLowerCase());
            // نستبعد الكلمات المفتاحية الشائعة من البحث الجزئي
            return word.length >= 3 && !commonKeywords.some(kw => normalizedWord === normalizeArabicText(kw.toLowerCase()));
        });
        
        for (const word of deptWords) {
            const normalizedWord = normalizeArabicText(word);
            const normalizedWordLower = normalizedWord.toLowerCase();
            
            // يجب أن تكون الكلمة موجودة بشكل واضح وليست مجرد كلمة مفتاحية
            if ((fuzzyMatchArabic(normalizedCommand, word) ||
                fuzzyMatchArabic(normalizedCommandLower, word) ||
                normalizedCommandNormalized.includes(normalizedWord) ||
                normalizedCommandLowerNormalized.includes(normalizedWordLower) ||
                processedCommandNormalized.includes(normalizedWord) ||
                processedCommandLowerNormalized.includes(normalizedWordLower)) &&
                // التأكد من أن الكلمة ليست مجرد كلمة مفتاحية
                !commonKeywords.some(kw => normalizeArabicText(kw.toLowerCase()) === normalizedWordLower)) {
                console.log('✓ Found department by word match (fuzzy):', dept.name, 'word:', word);
                return dept;
            }
        }
        
        // البحث بأسماء بديلة شائعة
        const aliases = getDepartmentAliases(deptName);
        for (const alias of aliases) {
            const normalizedAlias = normalizeArabicText(alias);
            const normalizedAliasLower = normalizedAlias.toLowerCase();
            
            if (fuzzyMatchArabic(normalizedCommand, alias) ||
                fuzzyMatchArabic(normalizedCommandLower, alias) ||
                normalizedCommandNormalized.includes(normalizedAlias) ||
                normalizedCommandLowerNormalized.includes(normalizedAliasLower) ||
                processedCommandNormalized.includes(normalizedAlias) ||
                processedCommandLowerNormalized.includes(normalizedAliasLower)) {
                console.log('✓ Found department by alias (fuzzy):', dept.name, 'alias:', alias);
                return dept;
            }
        }
        
        // البحث عن كلمات مفتاحية شائعة (دكاترة، أطباء، قسم، عيادة)
        // لكن فقط إذا كان هناك تطابق فعلي مع اسم القسم
        const keywords = ['دكاترة', 'دكتور', 'أطباء', 'طبيب', 'قسم', 'عيادة'];
        for (const keyword of keywords) {
            const normalizedKeyword = normalizeArabicText(keyword);
            if (normalizedCommandNormalized.includes(normalizedKeyword) || 
                normalizedCommandLowerNormalized.includes(normalizedKeyword.toLowerCase())) {
                // إذا وجدنا كلمة مفتاحية، يجب أن يكون هناك تطابق فعلي مع اسم القسم
                // لا نكتفي بوجود الكلمة المفتاحية فقط
                const keywordIndex = normalizedCommandNormalized.indexOf(normalizedKeyword);
                const afterKeyword = normalizedCommandNormalized.substring(keywordIndex + normalizedKeyword.length).trim();
                
                // يجب أن يكون اسم القسم موجوداً بعد الكلمة المفتاحية أو في النص بشكل واضح
                if (fuzzyMatchArabic(normalizedCommand, deptName) ||
                    fuzzyMatchArabic(normalizedCommandLower, deptNameLower) ||
                    fuzzyMatchArabic(afterKeyword, deptName) ||
                    normalizedCommandNormalized.includes(normalizedDeptName) ||
                    normalizedCommandLowerNormalized.includes(normalizedDeptNameLower) ||
                    afterKeyword.includes(normalizedDeptName) ||
                    afterKeyword.toLowerCase().includes(normalizedDeptNameLower)) {
                    console.log('✓ Found department by keyword + name (fuzzy):', dept.name);
                    return dept;
                }
            }
        }
    }
    
    console.log('No department found for command:', normalizedCommand);
    return null;
}

/**
 * الحصول على أسماء بديلة للقسم بناءً على الاسم الفعلي (للمرونة في التعرف)
 * هذه الدالة تولد مرادفات تلقائياً بناءً على الاسم الفعلي
 */
function getDepartmentAliases(deptName) {
    const aliases = [];
    
    // إزالة "ال" التعريف إذا كانت موجودة
    if (deptName.startsWith('ال')) {
        aliases.push(deptName.substring(2)); // بدون "ال"
    } else {
        aliases.push('ال' + deptName); // مع "ال"
    }
    
    // إضافة صيغ مختلفة
    // نأخذ الكلمات الرئيسية ونحاول إنشاء مرادفات
    const words = deptName.split(/\s+/);
    words.forEach(word => {
        if (word.length > 3) {
            // إزالة "ال" من الكلمة
            if (word.startsWith('ال')) {
                aliases.push(word.substring(2));
                aliases.push(word);
            } else {
                aliases.push('ال' + word);
            }
        }
    });
    
    // إزالة التكرارات
    return [...new Set(aliases)];
}

/**
 * استخراج اسم الطبيب من الأمر الصوتي
 * @param {string} command - الأمر الصوتي
 * @param {Array} departments - قائمة الأقسام (التي تحتوي على الأطباء)
 * @returns {Object|null} - الطبيب المطابق مع قسمه أو null
 */
export function extractDoctorFromCommand(command, departments) {
    if (!command || !command.trim()) {
        return null;
    }
    
    // تنظيف الأمر وإزالة الأحرف الزائدة
    let normalizedCommand = command.trim()
        .replace(/[^\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\uFB50-\uFDFF\uFE70-\uFEFFa-z0-9\s]/gi, '')
        .trim();
    
    const normalizedCommandLower = normalizedCommand.toLowerCase();
    
    console.log('Searching for doctor in command:', normalizedCommand);
    
    // جمع جميع الأطباء من جميع الأقسام
    const allDoctors = [];
    departments.forEach(dept => {
        if (dept.doctors && Array.isArray(dept.doctors)) {
            dept.doctors.forEach(doctor => {
                allDoctors.push({
                    ...doctor,
                    department: dept
                });
            });
        }
    });
    
    console.log('Available doctors:', allDoctors.map(d => d.name));
    
    // البحث عن الطبيب في الأمر
    for (const doctor of allDoctors) {
        const doctorName = doctor.name.trim();
        const doctorNameLower = doctorName.toLowerCase();
        
        // تطبيع الأسماء للبحث المرن
        const normalizedDoctorName = normalizeArabicText(doctorName);
        const normalizedDoctorNameLower = normalizedDoctorName.toLowerCase();
        const normalizedCommandNormalized = normalizeArabicText(normalizedCommand);
        const normalizedCommandLowerNormalized = normalizeArabicText(normalizedCommandLower);
        
        // استخراج كلمات اسم الطبيب للمقارنة المتعددة الكلمات
        const doctorWords = normalizedDoctorName.split(/\s+/).filter(w => w.length >= 3);
        
        // البحث المباشر بالاسم الكامل باستخدام التطبيع المرن
        if (fuzzyMatchArabic(normalizedCommand, doctorName) ||
            fuzzyMatchArabic(normalizedCommandLower, doctorNameLower) ||
            normalizedCommandNormalized.includes(normalizedDoctorName) ||
            normalizedCommandLowerNormalized.includes(normalizedDoctorNameLower)) {
            console.log('✓ Found doctor by direct match (fuzzy):', doctor.name);
            return { doctor, department: doctor.department };
        }
        
        // البحث بأجزاء من الاسم - لكن يجب أن تكون معظم كلمات الأمر موجودة في اسم الطبيب
        const commandWords = normalizedCommandNormalized.split(/\s+/).filter(w => w.length >= 3);
        // doctorWords تم تعريفه أعلاه في السطر 344
        
        // إزالة الكلمات المفتاحية من كلمات الأمر
        const doctorKeywords = ['دكتور', 'طبيب', 'د.', 'دك'];
        const filteredCommandWords = commandWords.filter(word => {
            const normalizedWord = normalizeArabicText(word.toLowerCase());
            return !doctorKeywords.some(kw => normalizedWord === normalizeArabicText(kw.toLowerCase()));
        });
        
        // إذا كان الأمر يحتوي على أكثر من كلمة (بعد إزالة الكلمات المفتاحية)
        if (filteredCommandWords.length >= 2) {
            // يجب أن تكون معظم كلمات الأمر موجودة في اسم الطبيب
            const matchingWords = filteredCommandWords.filter(cmdWord => {
                return doctorWords.some(docWord => {
                    const normalizedCmdWord = normalizeArabicText(cmdWord);
                    const normalizedDocWord = normalizeArabicText(docWord);
                    return fuzzyMatchArabic(cmdWord, docWord) ||
                           normalizedDocWord.includes(normalizedCmdWord) ||
                           normalizedCmdWord.includes(normalizedDocWord);
                });
            });
            
            // يجب أن تكون 75% على الأقل من كلمات الأمر موجودة في اسم الطبيب
            const matchRatio = matchingWords.length / filteredCommandWords.length;
            if (matchRatio >= 0.75 && matchingWords.length >= 2) {
                console.log('✓ Found doctor by multi-word match (fuzzy):', doctor.name, 'matched words:', matchingWords);
                return { doctor, department: doctor.department };
            }
        } else if (filteredCommandWords.length === 1) {
            // إذا كان الأمر يحتوي على كلمة واحدة فقط، يجب أن تكون موجودة في اسم الطبيب
            // لكن فقط إذا كانت الكلمة طويلة بما فيه الكفاية (أكثر من 4 أحرف)
            const singleWord = filteredCommandWords[0];
            if (singleWord.length > 4) {
                const normalizedSingleWord = normalizeArabicText(singleWord);
                for (const docWord of doctorWords) {
                    const normalizedDocWord = normalizeArabicText(docWord);
                    if (fuzzyMatchArabic(singleWord, docWord) ||
                        normalizedDocWord.includes(normalizedSingleWord) ||
                        normalizedSingleWord.includes(normalizedDocWord)) {
                        console.log('✓ Found doctor by single word match (fuzzy):', doctor.name, 'word:', singleWord);
                        return { doctor, department: doctor.department };
                    }
                }
            }
        }
        
        // البحث بكلمات مفتاحية شائعة (دكتور، طبيب) + الاسم
        // لكن يجب أن يكون هناك تطابق فعلي مع اسم الطبيب
        for (const keyword of doctorKeywords) {
            const normalizedKeyword = normalizeArabicText(keyword);
            if (normalizedCommandNormalized.includes(normalizedKeyword) || 
                normalizedCommandLowerNormalized.includes(normalizedKeyword.toLowerCase())) {
                // إذا وجدنا كلمة مفتاحية، نبحث عن اسم الطبيب في نفس الجملة
                // يجب أن يكون اسم الطبيب موجوداً بعد الكلمة المفتاحية
                const keywordIndex = normalizedCommandNormalized.indexOf(normalizedKeyword);
                const afterKeyword = normalizedCommandNormalized.substring(keywordIndex + normalizedKeyword.length).trim();
                
                // يجب أن يكون اسم الطبيب موجوداً بعد الكلمة المفتاحية
                // ونستخدم نفس منطق البحث المتعدد الكلمات
                const commandWords = afterKeyword.split(/\s+/).filter(w => w.length >= 3);
                
                if (commandWords.length >= 2) {
                    // يجب أن تكون معظم كلمات الأمر موجودة في اسم الطبيب
                    const matchingWords = commandWords.filter(cmdWord => {
                        return doctorWords.some(docWord => {
                            const normalizedCmdWord = normalizeArabicText(cmdWord);
                            const normalizedDocWord = normalizeArabicText(docWord);
                            return fuzzyMatchArabic(cmdWord, docWord) ||
                                   normalizedDocWord.includes(normalizedCmdWord) ||
                                   normalizedCmdWord.includes(normalizedDocWord);
                        });
                    });
                    
                    // يجب أن تكون 75% على الأقل من كلمات الأمر موجودة في اسم الطبيب
                    const matchRatio = matchingWords.length / commandWords.length;
                    if (matchRatio >= 0.75 && matchingWords.length >= 2) {
                        console.log('✓ Found doctor by keyword + multi-word match (fuzzy):', doctor.name, 'matched words:', matchingWords);
                        return { doctor, department: doctor.department };
                    }
                } else if (fuzzyMatchArabic(afterKeyword, doctorName) ||
                          fuzzyMatchArabic(afterKeyword.toLowerCase(), doctorNameLower) ||
                          afterKeyword.includes(normalizedDoctorName) ||
                          afterKeyword.toLowerCase().includes(normalizedDoctorNameLower)) {
                    console.log('✓ Found doctor by keyword + name (fuzzy):', doctor.name);
                    return { doctor, department: doctor.department };
                }
            }
        }
    }
    
    console.log('No doctor found for command:', normalizedCommand);
    return null;
}

/**
 * معالجة الأمر الصوتي وإرجاع الإجراء المطلوب
 * @param {string} command - الأمر الصوتي
 * @param {Array} departments - قائمة الأقسام (التي تحتوي على الأطباء)
 * @returns {Object} - الإجراء المطلوب { type: 'department'|'doctor'|null, data: Object|null }
 */
export function processVoiceCommand(command, departments) {
    if (!command || !command.trim()) {
        return { type: null, data: null };
    }
    
    // أولاً: البحث عن طبيب (أولوية أعلى لأن البحث عن الطبيب أكثر تحديداً)
    const doctorResult = extractDoctorFromCommand(command, departments);
    if (doctorResult) {
        return {
            type: 'doctor',
            data: {
                doctor: doctorResult.doctor,
                department: doctorResult.department
            },
        };
    }
    
    // ثانياً: البحث عن قسم
    const department = extractDepartmentFromCommand(command, departments);
    if (department) {
        return {
            type: 'department',
            data: department,
        };
    }
    
    return { type: null, data: null };
}

/**
 * تهيئة Web Speech Recognition API
 * @returns {SpeechRecognition|null} - SpeechRecognition object أو null إذا لم يكن مدعوم
 */
export function initSpeechRecognition() {
    // التحقق من دعم المتصفح
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    
    if (!SpeechRecognition) {
        console.warn('Speech Recognition API is not supported in this browser');
        return null;
    }
    
    try {
        const recognition = new SpeechRecognition();
        
        // محاولة استخدام اللغة العربية - نبدأ بـ 'ar' (أكثر توافقاً من 'ar-SA')
        // إذا فشلت، سنحاول 'ar-EG' أو 'ar-XA' كبدائل
        recognition.lang = 'ar'; // اللغة العربية العامة (أكثر توافقاً)
        
        recognition.continuous = false; // توقف بعد نهاية الكلام
        recognition.interimResults = false; // النتائج النهائية فقط
        recognition.maxAlternatives = 3; // زيادة البدائل لتحسين التعرف
        
        return recognition;
    } catch (error) {
        console.error('Error initializing Speech Recognition:', error);
        return null;
    }
}

