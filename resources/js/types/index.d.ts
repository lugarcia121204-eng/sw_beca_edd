/**
 * Global Type Definitions for BecasLaravel Application
 * This file provides TypeScript definitions for the application
 */

// Ziggy route helper
declare function route(name?: string, params?: any, absolute?: boolean): string;

// Inertia types
declare module '@inertiajs/vue3' {
    export interface PageProps {
        auth: {
            user: User;
            layoutName: string;
            role: string;
            permissions: Record<string, boolean>;
            roles: Record<string, boolean>;
        };
        flash: {
            success?: string;
            error?: string;
            warning?: string;
            info?: string;
        };
        errors: Record<string, string>;
        [key: string]: any;
    }
}

// User type
interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    curp?: string;
    institution_id?: number;
    priority_area_id?: number;
    sub_area_id?: number;
    institution?: Institution;
    priority_area?: PriorityArea;
    sub_area?: SubArea;
    roles?: Role[];
    permissions?: Permission[];
    created_at: string;
    updated_at: string;
}

// Institution type
interface Institution {
    id: number;
    name: string;
    state_id: number;
    state?: State;
}

// State type
interface State {
    id: number;
    name: string;
    abbreviation: string;
}

// Priority Area type
interface PriorityArea {
    id: number;
    name: string;
    description?: string;
}

// Sub Area type
interface SubArea {
    id: number;
    name: string;
    priority_area_id: number;
    priority_area?: PriorityArea;
}

// Role type
interface Role {
    id: number;
    name: string;
    guard_name: string;
}

// Permission type
interface Permission {
    id: number;
    name: string;
    description?: string;
    module_key?: string;
    guard_name: string;
}

// Announcement type
interface Announcement {
    id: number;
    title: string;
    description?: string;
    status: 'activa' | 'pendiente' | 'cerrada';
    file_path?: string;
    calendar_id?: number;
    calendar?: Calendar;
    created_at: string;
    updated_at: string;
}

// Calendar type
interface Calendar {
    id: number;
    announcement_id: number;
    registration_start_date: string;
    registration_end_date: string;
    evaluation_start_date: string;
    evaluation_end_date: string;
    results_date: string;
}

// Application type
interface Application {
    id: number;
    user_id: number;
    announcement_id: number;
    status: string;
    user?: User;
    announcement?: Announcement;
    evaluations?: Evaluation[];
    documents?: Document[];
}

// Evaluation type
interface Evaluation {
    id: number;
    application_id: number;
    evaluator_id: number;
    rubric_id: number;
    total_score?: number;
    status: string;
    comments?: string;
    evaluated_at?: string;
    evaluator?: User;
    application?: Application;
    rubric?: Rubric;
}

// Document type
interface Document {
    id: number;
    application_id: number;
    catalog_document_id: number;
    file_path: string;
    file_name: string;
    mime_type: string;
    catalog_document?: CatalogDocument;
}

// Catalog Document type
interface CatalogDocument {
    id: number;
    name: string;
    description?: string;
    is_required: boolean;
    is_active: boolean;
}

// Rubric type
interface Rubric {
    id: number;
    name: string;
    description?: string;
    is_active: boolean;
    questions?: RubricQuestion[];
}

// Rubric Question type
interface RubricQuestion {
    id: number;
    rubric_id: number;
    question_text: string;
    order: number;
    options?: RubricQuestionOption[];
}

// Rubric Question Option type
interface RubricQuestionOption {
    id: number;
    question_id: number;
    option_text: string;
    score: number;
    order: number;
}

// Recognition type
interface Recognition {
    id: number;
    user_id: number;
    announcement_id: number;
    active: boolean;
    user?: User;
    announcement?: Announcement;
}

// Module type
interface Module {
    id: number;
    name: string;
    key: string;
    description?: string;
    icon?: string;
}

// Pagination type
interface Pagination<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
    links: PaginationLink[];
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export {};
