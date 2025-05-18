<li class="nav-item dropdown notification-dropdown mt-2 ms-2 ms-sm-0">
    <a class="nav-link position-relative" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-bell fs-3"></i>
        <span class="badge badge-danger notification-badge" style="display: none;"></span>
    </a>
    <ul class="dropdown-menu dropdown-menu-end notification-menu" aria-labelledby="notificationDropdown" style="max-height: 400px; overflow-y: auto;">
        <div id="notification-list">
            <li class="dropdown-item text-center">Loading notifications...</li>
        </div>
        <li id="load-more-container" class="dropdown-item text-center" style="display: none;">
            <button id="load-more-notifications" class="btn btn-link">Load More</button>
        </li>
    </ul>
</li>

<style>
.notification-menu {
    width: 350px;
    max-height: 400px;
    overflow-y: auto;
}
.notification-item {
    padding: 10px;
    transition: background-color 0.2s;
}
.notification-item.unread {
    background-color: #f1f3f5; /* Slight greyish background for unread */
}
.notification-item:hover {
    background-color: #f8f9fa;
}
.notification-message {
    word-break: break-word;
    white-space: normal;
    overflow-wrap: anywhere;
}
.delete-notification {
    padding: 2px 6px;
    line-height: 1;
}
.notification-item-wrapper {
    position: relative;
}
.alert {
    margin: 10px;
    padding: 10px;
    border-radius: 4px;
}
.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const notificationMenu = document.querySelector('.notification-menu');
    const notificationList = document.querySelector('#notification-list');
    const loadMoreContainer = document.querySelector('#load-more-container');
    let currentPage = 1;
    const perPage = 10;
    let status = '';

    // Fetch notifications
    const fetchNotifications = (page = 1, append = false) => {
        fetch(`<?= site_url('user/notification') ?>?page=${page}&perPage=${perPage}${status ? `&status=${status}` : ''}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(async response => {
            const contentType = response.headers.get('content-type');
            if (!response.ok) {
                const errorData = contentType && contentType.includes('application/json') ? await response.json() : {};
                const errorMessage = errorData.message || `HTTP error ${response.status}`;
                if (response.status === 401) {
                    throw new Error('Please log in to view notifications.');
                }
                throw new Error(errorMessage);
            }
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Invalid response format: Expected JSON');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                if (!append) {
                    notificationList.innerHTML = '';
                }
                if (data.data.length === 0) {
                    notificationList.innerHTML = '<li class="dropdown-item text-center">No new notifications</li>';
                } else {
                    data.data.forEach(notification => {
                        const li = document.createElement('li');
                        li.className = 'notification-item-wrapper';
                        li.innerHTML = `
                            <div class="dropdown-item notification-item d-flex gap-2 align-items-start ${notification.is_read == 0 ? 'unread' : ''}" data-notification-id="${notification.notification_id}">
                                <i class="fas ${notification.type ? getNotificationIcon(notification.type) : 'fa-bell'}"></i>
                                <div class="d-flex flex-column flex-grow-1">
                                    <small class="notification-message">${notification.message}</small>
                                    <small class="notification-time text-muted">${new Date(notification.created_at).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })}</small>
                                </div>
                                <button class="btn btn-sm btn-danger delete-notification" data-notification-id="${notification.notification_id}" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        `;
                        notificationList.appendChild(li);
                        li.querySelector('.notification-item').addEventListener('click', () => handleNotificationClick(li.querySelector('.notification-item')));
                        li.querySelector('.delete-notification').addEventListener('click', () => handleDeleteClick(li.querySelector('.delete-notification')));
                    });
                }
                updateNotificationBadge(data.notificationCount);
                loadMoreContainer.style.display = data.pagination.hasMore ? 'block' : 'none';
                if (data.pagination.hasMore) {
                    document.querySelector('#load-more-notifications').setAttribute('data-page', page + 1);
                }
            } else {
                throw new Error(data.message || 'Error loading notifications');
            }
        })
        .catch(error => {
            console.error('Error fetching notifications:', error.message, error);
            notificationList.innerHTML = `<li class="dropdown-item text-center alert alert-danger">${error.message || 'Error loading notifications'}</li>`;
        });
    };

    // Handle notification click (mark as read)
    const handleNotificationClick = (item) => {
        const notificationId = item.getAttribute('data-notification-id');
        performAction('markAsRead', notificationId, item.closest('.notification-item-wrapper'));
    };

    // Handle delete button click
    const handleDeleteClick = (button) => {
        const notificationId = button.getAttribute('data-notification-id');
        performAction('delete', notificationId, button.closest('.notification-item-wrapper'));
    };

    // Perform action (markAsRead or delete)
    const performAction = (action, notificationId, wrapper) => {
        const postData = {
            action: action,
            notification_id: parseInt(notificationId),
            [<?= json_encode(csrf_token()) ?>]: <?= json_encode(csrf_hash()) ?>
        };
        console.log('POST request body:', postData);

        fetch('<?= site_url('user/notification') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(postData)
        })
        .then(async response => {
            const contentType = response.headers.get('content-type');
            if (!response.ok) {
                const errorData = contentType && contentType.includes('application/json') ? await response.json() : {};
                const errorMessage = errorData.message || `HTTP error ${response.status}`;
                if (response.status === 401) {
                    throw new Error('Please log in to perform this action.');
                } else if (response.status === 403) {
                    throw new Error('CSRF token validation failed.');
                }
                throw new Error(errorMessage);
            }
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Invalid response format: Expected JSON');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                wrapper.remove();
                if (notificationList.children.length === 0) {
                    notificationList.innerHTML = '<li class="dropdown-item text-center">No new notifications</li>';
                }
                fetchNotifications(currentPage, false); // Refresh the list
            } else {
                throw new Error(data.message || `Failed to ${action} notification`);
            }
        })
        .catch(error => {
            console.error(`Error performing ${action}:`, error.message, error);
            notificationList.innerHTML = `<li class="dropdown-item text-center alert alert-danger">${error.message || `Error performing ${action}`}</li>`;
        });
    };

    // Update notification badge
    const updateNotificationBadge = (count) => {
        const badge = document.querySelector('.notification-badge');
        if (count > 0) {
            if (!badge) {
                const newBadge = document.createElement('span');
                newBadge.className = 'badge badge-danger notification-badge';
                newBadge.textContent = count;
                document.querySelector('#notificationDropdown').appendChild(newBadge);
            } else {
                badge.textContent = count;
                badge.style.display = 'block';
            }
        } else {
            if (badge) {
                badge.style.display = 'none';
            }
        }
    };

    // Load more notifications
    const loadMoreNotifications = () => {
        const loadMoreButton = document.querySelector('#load-more-notifications');
        if (!loadMoreButton) return;

        const nextPage = parseInt(loadMoreButton.getAttribute('data-page'));
        fetchNotifications(nextPage, true);
        currentPage = nextPage;
    };

    // Initialize: Fetch first page of notifications
    fetchNotifications();

    // Event listener for load more
    document.querySelector('#load-more-notifications')?.addEventListener('click', loadMoreNotifications);

    // Helper for getNotificationIcon
    function getNotificationIcon(type) {
        const icons = {
            'info': 'fa-info-circle',
            'warning': 'fa-exclamation-triangle',
            'error': 'fa-times-circle',
            'success': 'fa-check-circle'
        };
        return icons[type] || 'fa-bell';
    }
});
</script>