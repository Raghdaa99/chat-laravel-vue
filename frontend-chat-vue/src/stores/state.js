export default {
    User: {
        id: Number,
        name: String,
        avatar_url: String,
        created_at: String,
        email: String,
        last_seen: String,
        updated_at: String,
    },

    Conversation: {
        id: Number,
        label: null,
        new_messages: Number,
        type: null,
        user_id: Number,
        last_message: {
            id: Number,
            body: null,
            created_at: null,
            deleted_at: null,
            type: null,
            updated_at: null,
            user_id: Number
        },
        participants: []
    },

    Message: {
        id: Number,
        body: null,
        conversation_id: Number,
        created_at: null,
        deleted_at: null,
        type: null,
        updated_at: null,
        user_id: Number,
        user: {}
    }

}
