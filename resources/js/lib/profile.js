export const normalizeStatus = (status) => {
    if (!status) return 'Backlog'

    const mappedStatuses = {
        backlog: 'Backlog',
        playing: 'Playing',
        finished: 'Finished',
        completed: 'Completed',
        wishlist: 'Wishlist',
        dropped: 'Dropped',
    }

    return mappedStatuses[status.toLowerCase()] ?? status
}

export const ratingStars = (rating) => {
    if (!rating) return '—'

    return '★'.repeat(rating)
}

export const statusColor = (item) => {
    return item?.status_color ?? '#71717a'
}