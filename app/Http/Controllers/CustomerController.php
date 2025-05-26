<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerContact;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB; // For transactions

class CustomerController extends Controller {

    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->has('region')) {
            $query->where('region', $request->input('region'));
        }
        if ($request->has('industry')) {
            $query->where('industry', $request->input('industry'));
        }
        if ($request->has('customer_type')) {
            $query->where('customer_type', $request->input('customer_type'));
        }

        // Add search functionality
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', '%' . $search . '%')
                  ->orWhere('last_name', 'like', '%' . $search . '%')
                  ->orWhere('company_name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $customers = $query->paginate($request->input('per_page', 15)); // Pagination

        return response()->json($customers);
    }

    /**
     * Get a specific customer by ID.
     * GET /api/v1/customers/{id}
     */
    public function show($id)
    {
        // Eager load contacts for the customer detail page
        $customer = Customer::with('contacts')->find($id);

        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        return response()->json($customer);
    }

    /**
     * Create a new customer.
     * Rule 1.1: Store in centralized DB.
     * Rule 1.2: Include key info.
     * Rule 1.3: Credit limit based on policies.
     * Rule 1.4: Approval workflow for changes (simplified here, more complex in real app).
     * POST /api/v1/customers
     */
    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'company_name' => 'nullable|string|max:255',
                'email' => 'required|email|unique:customers,email',
                'phone_number' => 'nullable|string|max:50',
                'billing_address_line1' => 'required|string|max:255',
                'billing_city' => 'required|string|max:100',
                'billing_state' => 'required|string|max:100',
                'billing_zip_code' => 'required|string|max:20',
                'billing_country' => 'required|string|max:100',
                'shipping_address_line1' => 'required|string|max:255',
                'shipping_city' => 'required|string|max:100',
                'shipping_state' => 'required|string|max:100',
                'shipping_zip_code' => 'required|string|max:20',
                'shipping_country' => 'required|string|max:100',
                'credit_limit' => 'nullable|numeric|min:0', // Rule 1.3
                'customer_type' => 'required|string|in:Individual,Business', // Rule 1.6
                'industry' => 'nullable|string|max:100',
                'region' => 'nullable|string|max:100',
                'contacts' => 'nullable|array', // For Rule 1.2: nested contacts
                'contacts.*.contact_name' => 'required_with:contacts|string|max:255',
                'contacts.*.contact_email' => 'nullable|email|max:255',
                'contacts.*.contact_phone' => 'nullable|string|max:50',
                'contacts.*.contact_role' => 'nullable|string|max:100',
            ]);

            DB::beginTransaction();

            $customer = Customer::create($request->except('contacts'));

            if ($request->has('contacts')) {
                foreach ($request->input('contacts') as $contactData) {
                    $customer->contacts()->create($contactData);
                }
            }

            // Simplified approval workflow for illustration:
            // In a real system, this would involve status fields, notifications, etc.
            // For now, we'll assume direct creation or log for review.
            // Add to audit logs (Rule 1.4) - manual entry for demonstration
            // (ideally this is handled by database triggers or a service layer)
            /*
            \App\Models\AuditLog::create([
                'table_name' => 'customers',
                'record_id' => $customer->customer_id,
                'action_type' => 'INSERT',
                'new_value' => $customer->toArray(),
                'changed_by' => 'System/User ID', // Replace with actual user ID
            ]);
            */

            DB::commit();

            return response()->json($customer->load('contacts'), 201); // 201 Created
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create customer: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update an existing customer.
     * Rule 1.4: Changes trigger approval workflow.
     * PUT /api/v1/customers/{id}
     */
    public function update(Request $request, $id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        try {
            $this->validate($request, [
                'first_name' => 'string|max:100',
                'last_name' => 'string|max:100',
                'company_name' => 'nullable|string|max:255',
                'email' => 'email|unique:customers,email,' . $customer->customer_id . ',customer_id', // Exclude self
                'phone_number' => 'nullable|string|max:50',
                'billing_address_line1' => 'string|max:255',
                // ... include other fields for validation
                'credit_limit' => 'nullable|numeric|min:0',
                'customer_type' => 'string|in:Individual,Business',
                'industry' => 'nullable|string|max:100',
                'region' => 'nullable|string|max:100',
            ]);

            DB::beginTransaction();

            $oldValues = $customer->toArray(); // Capture old values for audit log

            $customer->update($request->all());

            // Simplified approval workflow for illustration:
            // In a real system, this would involve setting a 'pending_approval' flag
            // and the frontend showing "Changes pending approval". Actual update happens after approval.
            // For now, we'll just update directly.
            // Add to audit logs (Rule 1.4)
            /*
            \App\Models\AuditLog::create([
                'table_name' => 'customers',
                'record_id' => $customer->customer_id,
                'action_type' => 'UPDATE',
                'old_value' => array_diff_assoc($oldValues, $customer->toArray()), // Only changed fields
                'new_value' => array_intersect_key($customer->toArray(), $request->all()), // Only submitted fields
                'changed_by' => 'System/User ID', // Replace with actual user ID
            ]);
            */

            DB::commit();

            return response()->json($customer);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update customer: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete a customer.
     * DELETE /api/v1/customers/{id}
     */
    public function destroy($id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        // Before deleting, consider soft deletes or checking for related records (orders, invoices)
        // to prevent data integrity issues. For this example, we'll allow cascade deletes due to FK setup.
        DB::beginTransaction();
        try {
            $customer->delete();
            DB::commit();
            return response()->json(['message' => 'Customer deleted successfully'], 204); // 204 No Content
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to delete customer. Ensure no related orders/invoices exist.'], 500);
        }
    }
}