package com.clinic.screen.ui.inquiry.adapters

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.TextView
import androidx.cardview.widget.CardView
import androidx.recyclerview.widget.RecyclerView
import com.clinic.screen.R
import com.clinic.screen.data.model.Department

class DepartmentsAdapter(
    private val departments: List<Department>,
    private val onItemClick: (Department) -> Unit
) : RecyclerView.Adapter<DepartmentsAdapter.DepartmentViewHolder>() {

    inner class DepartmentViewHolder(itemView: View) : RecyclerView.ViewHolder(itemView) {
        val cardView: CardView = itemView.findViewById(R.id.departmentCard)
        val nameTextView: TextView = itemView.findViewById(R.id.departmentName)
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): DepartmentViewHolder {
        val view = LayoutInflater.from(parent.context)
            .inflate(R.layout.item_department, parent, false)
        return DepartmentViewHolder(view)
    }

    override fun onBindViewHolder(holder: DepartmentViewHolder, position: Int) {
        val department = departments[position]
        holder.nameTextView.text = department.name
        
        holder.cardView.setOnClickListener {
            onItemClick(department)
        }
    }

    override fun getItemCount(): Int = departments.size
}

