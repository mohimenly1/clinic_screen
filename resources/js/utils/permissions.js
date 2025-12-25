import { usePage } from '@inertiajs/vue3';

/**
 * التحقق من أن المستخدم لديه صلاحية معينة
 * @param {string} permission - اسم الصلاحية
 * @returns {boolean}
 */
export function hasPermission(permission) {
    const page = usePage();
    const user = page.props.auth?.user;
    const permissions = page.props.auth?.permissions || [];

    // إذا كان المستخدم مدير، لديه جميع الصلاحيات
    if (user?.is_admin) {
        return true;
    }

    return permissions.includes(permission);
}

/**
 * التحقق من أن المستخدم لديه أي من الصلاحيات المحددة
 * @param {string[]} permissions - قائمة الصلاحيات
 * @returns {boolean}
 */
export function hasAnyPermission(permissions) {
    return permissions.some(permission => hasPermission(permission));
}

/**
 * التحقق من أن المستخدم لديه جميع الصلاحيات المحددة
 * @param {string[]} permissions - قائمة الصلاحيات
 * @returns {boolean}
 */
export function hasAllPermissions(permissions) {
    return permissions.every(permission => hasPermission(permission));
}

