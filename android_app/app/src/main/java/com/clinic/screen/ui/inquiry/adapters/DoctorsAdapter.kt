package com.clinic.screen.ui.inquiry.adapters

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ImageView
import android.widget.TextView
import androidx.cardview.widget.CardView
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide
import com.clinic.screen.R
import com.clinic.screen.data.model.Doctor
import com.clinic.screen.util.ApiConfig

class DoctorsAdapter(
    private val doctors: List<Doctor>,
    private val onItemClick: (Doctor) -> Unit
) : RecyclerView.Adapter<DoctorsAdapter.DoctorViewHolder>() {

    inner class DoctorViewHolder(itemView: View) : RecyclerView.ViewHolder(itemView) {
        val cardView: CardView = itemView.findViewById(R.id.doctorCard)
        val nameTextView: TextView = itemView.findViewById(R.id.doctorName)
        val photoImageView: ImageView = itemView.findViewById(R.id.doctorPhoto)
        val schedulesCountTextView: TextView = itemView.findViewById(R.id.schedulesCount)
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): DoctorViewHolder {
        val view = LayoutInflater.from(parent.context)
            .inflate(R.layout.item_doctor, parent, false)
        return DoctorViewHolder(view)
    }

    override fun onBindViewHolder(holder: DoctorViewHolder, position: Int) {
        val doctor = doctors[position]
        holder.nameTextView.text = doctor.name
        
        // Load doctor photo if available
        doctor.photoUrl?.let { photoUrl ->
            val fullUrl = if (photoUrl.startsWith("http")) photoUrl else "${ApiConfig.BASE_URL}$photoUrl"
            Glide.with(holder.itemView.context)
                .load(fullUrl)
                .placeholder(R.drawable.ic_doctor_placeholder)
                .error(R.drawable.ic_doctor_placeholder)
                .into(holder.photoImageView)
        } ?: run {
            holder.photoImageView.setImageResource(R.drawable.ic_doctor_placeholder)
        }
        
        // Show schedules count
        val schedulesCount = doctor.schedules?.size ?: 0
        holder.schedulesCountTextView.text = "$schedulesCount موعد"
        holder.schedulesCountTextView.visibility = if (schedulesCount > 0) View.VISIBLE else View.GONE
        
        holder.cardView.setOnClickListener {
            onItemClick(doctor)
        }
    }

    override fun getItemCount(): Int = doctors.size
}

